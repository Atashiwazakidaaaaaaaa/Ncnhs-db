<?php
// --- ANNOUNCEMENT UPDATE ENDPOINT: CLEAN REBUILD ---

// CORS and JSON headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "CORS preflight"]);
    exit();
}

// --- ERROR HANDLING & LOGGING ---
ini_set('display_errors', 0); // Disable for production, use logs instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_reporting(E_ALL);

// Helper to send JSON response and exit
function send_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// --- DATABASE CONNECTION ---
$host = 'localhost';
$db   = 'ncnhsdb';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Database Connection Failed: ' . $e->getMessage());
    send_json(['success' => false, 'error' => 'A database error occurred.'], 500);
}

// --- MAIN LOGIC ---
try {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    if ($requestMethod !== 'POST' && $requestMethod !== 'PUT') {
        send_json(['success' => false, 'message' => 'Invalid request method.'], 405);
    }

    $isMultipart = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;

    // Initialize variables
    $id = null;
    $title = null;
    $content = null;
    $display_date = null;
    $category = null;
    $image_filename = null;
    $remove_image = false;

    if ($isMultipart) {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;
        $display_date = $_POST['display_date'] ?? null;
        $category = $_POST['category'] ?? null;
        $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] === 'true';

        // --- FILE UPLOAD HANDLING ---
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
                send_json(['success' => false, 'message' => 'Invalid file type.'], 400);
            }
            if ($file['size'] > $maxSize) {
                send_json(['success' => false, 'message' => 'File is too large.'], 400);
            }

            // Generate a unique filename to prevent overwrites. [3, 7]
            $path_info = pathinfo($file['name']);
            $extension = $path_info['extension'];
            $image_filename = uniqid('img_', true) . '.' . $extension;
            $upload_path = __DIR__ . '/uploads/' . $image_filename;

            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                send_json(['success' => false, 'message' => 'Failed to move uploaded file.'], 500);
            }
        }
    } else { // Handle JSON payload
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            send_json(['success' => false, 'message' => 'Invalid JSON received.'], 400);
        }
        $id = $input['id'] ?? null;
        $title = $input['title'] ?? null;
        $content = $input['content'] ?? null;
        $display_date = $input['display_date'] ?? null;
        $category = $input['category'] ?? null;
        $remove_image = isset($input['remove_image']) && $input['remove_image'] === true;
    }

    // --- VALIDATION ---
    if (empty($id) || empty($title) || empty($content) || empty($display_date) || empty($category)) {
        send_json(['success' => false, 'message' => 'Missing required fields.'], 400);
    }

    // --- DATABASE UPDATE ---
    $sql_parts = [];
    $params = [];

    $sql_parts[] = "title=?";
    $params[] = $title;
    $sql_parts[] = "content=?";
    $params[] = $content;
    $sql_parts[] = "display_date=?";
    $params[] = $display_date;
    $sql_parts[] = "category=?";
    $params[] = $category;
    $sql_parts[] = "updated_at=NOW()";

    if ($image_filename) {
        // If a new image is uploaded, update the attachment field. [18]
        $sql_parts[] = "attachment=?";
        $params[] = $image_filename;
    } elseif ($remove_image) {
        // If remove_image is flagged, set attachment to NULL.
        $sql_parts[] = "attachment=NULL";
    }
    // If neither of the above, the attachment field is left unchanged. [23]

    if (empty($sql_parts)) {
        send_json(['success' => true, 'message' => 'No fields to update.']);
    }

    $sql = "UPDATE announcements SET " . implode(', ', $sql_parts) . " WHERE id=?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
        send_json([
            'success' => true,
            'message' => 'Announcement updated successfully.',
            'affected_rows' => $rowCount,
            'filename' => $image_filename
        ]);
    } else {
        // This could mean the record with the given ID was not found,
        // or the submitted data was identical to the existing data.
        send_json([
            'success' => false,
            'message' => 'No changes were made. The announcement may not exist or the data was the same.',
            'affected_rows' => 0
        ], 404);
    }

} catch (PDOException $e) {
    error_log('PDO Exception: ' . $e->getMessage());
    send_json(['success' => false, 'error' => 'A database error occurred during the update.'], 500);
} catch (Throwable $e) {
    error_log('General Exception: ' . $e->getMessage());
    send_json(['success' => false, 'error' => 'An unexpected error occurred.'], 500);
}
<?php
// Enable CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

// Check if database connection failed
if ($pdo === null) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => isset($db_error) ? $db_error : 'Database connection failed'
    ]);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $section_name = $input['section_name'] ?? '';
    $info = $input['Info'] ?? '';
    
    if (empty($section_name) || empty($info)) {
        throw new Exception('Section name and info are required');
    }
    
    // Update the content in database
    $stmt = $pdo->prepare("UPDATE about_content SET Info = ? WHERE section_name = ?");
    $stmt->execute([$info, $section_name]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Content updated successfully'
        ]);
    } else {
        // If no rows were updated, the section might not exist
        echo json_encode([
            'success' => false,
            'error' => 'Section not found or no changes made'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

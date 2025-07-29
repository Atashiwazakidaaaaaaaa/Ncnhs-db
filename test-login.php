<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'This endpoint only accepts POST requests.']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default password
$dbname = "ncnhsdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$submittedUsername = $input['username'] ?? '';
$submittedPassword = $input['password'] ?? '';

// For debugging, let's log what we receive
error_log("Login attempt: username=" . $submittedUsername . ", password=" . $submittedPassword);

// Query for user
try {
    $stmt = $conn->prepare("SELECT * FROM usercreds WHERE username = ?");
    $stmt->bind_param("s", $submittedUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User not found
        echo json_encode([
            'success' => false,
            'error' => 'Invalid username or password'
        ]);
    } else {
        // User exists, check password
        $adminUser = $result->fetch_assoc();
        
        if (password_verify($submittedPassword, $adminUser['password'])) {
            echo json_encode([
                'success' => true,
                'admin' => [
                    'id' => $adminUser['id'] ?? null,
                    'username' => $adminUser['username'],
                    'email' => $adminUser['email'] ?? ($adminUser['username'] . '@ncnhs.edu')
                ],
                'token' => bin2hex(random_bytes(16))
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid username or password'
            ]);
        }
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

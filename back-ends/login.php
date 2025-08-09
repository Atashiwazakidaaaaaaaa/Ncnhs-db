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

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get form data and log for debugging
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Debug logging
    error_log("Login attempt - Username: " . $username . ", Password length: " . strlen($password));
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        exit();
    }
    
    // Use the same config as other backend files
    require_once 'config.php';
    
    if ($pdo === null) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => isset($db_error) ? $db_error : 'Database connection failed'
        ]);
        exit();
    }
    
    // Get user from database, handle missing table gracefully
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, email FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database query error: ' . $e->getMessage()
        ]);
        exit();
    }
    
    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        // Failed login
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

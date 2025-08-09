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
        'error' => isset($db_error) ? $db_error : 'Database connection failed'
    ]);
    exit();
}

try {
    // Fetch all about content from database
    $stmt = $pdo->prepare("SELECT section_name, Info FROM about_content ORDER BY id");
    $stmt->execute();
    $content = $stmt->fetchAll();
    
    // Return JSON response
    echo json_encode($content);
    
} catch (PDOException $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Return generic error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

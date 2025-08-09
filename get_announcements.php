<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ncnhsdb";

try {
    // Create connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get announcements ordered by creation date (newest first)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            content,
            display_date,
            category,
            priority,
            author,
            date_posted,
            created_at,
            is_active
        FROM announcements 
        WHERE is_active = 1 
        ORDER BY created_at DESC
    ");
    
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log for debugging
    error_log("Announcements fetched: " . count($announcements));
    
    // Format dates for frontend
    foreach ($announcements as &$announcement) {
        if ($announcement['date_posted']) {
            $announcement['formatted_date'] = date('F j, Y', strtotime($announcement['date_posted']));
        } else {
            $announcement['formatted_date'] = $announcement['display_date'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $announcements,
        'count' => count($announcements)
    ]);
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>

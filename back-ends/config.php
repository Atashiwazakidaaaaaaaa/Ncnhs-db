<?php
// Database configuration
$host = 'localhost';        // Standard MySQL host
$dbname = 'ncnhsdb';  // Unified database name (was ncnhs_website in some scripts)
$username = 'root';         // Change this to your database username
$password = 'root';         // MAMP default password

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Don't die immediately - let the calling script handle the error
    $pdo = null;
    $db_error = "Database connection failed: " . $e->getMessage();
}
?>

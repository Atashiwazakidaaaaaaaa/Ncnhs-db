<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ncnhsdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute the SQL file
    $sql = file_get_contents('sample_announcements.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Sample announcements imported successfully!";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM announcements WHERE is_active = 1");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\nTotal active announcements: " . $count;
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

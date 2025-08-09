<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ncnhsdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database: $dbname\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents('fixed_sample_announcements.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "Fixed sample announcements imported successfully!\n";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM announcements WHERE is_active = 1");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total active announcements: " . $count . "\n";
    
    // Show a sample
    $stmt = $pdo->query("SELECT id, title, date_posted FROM announcements WHERE is_active = 1 LIMIT 3");
    $sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample announcements:\n";
    foreach ($sample as $ann) {
        echo "ID: {$ann['id']}, Title: {$ann['title']}, Date: {$ann['date_posted']}\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

<?php
echo "Setting up NCNHS database and admin user...\n";

$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default password
$dbname = "ncnhsdb";

try {
    // Connect to MySQL (without database)
    $pdo = new PDO("mysql:host=$servername", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Database '$dbname' created or already exists.\n";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create admin_users table (for login.php compatibility)
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100)
    )";
    $pdo->exec($sql);
    echo "admin_users table created or already exists.\n";
    
    // Create about_content table (for about page)
    $sql = "CREATE TABLE IF NOT EXISTS about_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_name VARCHAR(50) NOT NULL,
        Info TEXT NOT NULL
    )";
    $pdo->exec($sql);
    echo "about_content table created or already exists.\n";
    
    // Insert default about content if not exists
    $defaultContent = [
        ['vision', 'Our vision content goes here.'],
        ['mission', 'Our mission statement goes here.'],
        ['core_values', 'Our core values are listed here.'],
        ['ncnhs_about', 'Information about New Cabalan National High School goes here.']
    ];
    
    foreach ($defaultContent as $content) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM about_content WHERE section_name = ?");
        $stmt->execute([$content[0]]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO about_content (section_name, Info) VALUES (?, ?)");
            $stmt->execute($content);
            echo "Inserted default content for: " . $content[0] . "\n";
        }
    }
    
    // Create admin user
    $adminUsername = "admin";
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT); // Simple password, change this!
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$adminUsername]);
    
    if ($stmt->rowCount() > 0) {
        // Update existing admin user
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
        $stmt->execute([$adminPassword, $adminUsername]);
        echo "Admin user password updated successfully!\n";
    } else {
        // Insert new admin user
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$adminUsername, $adminPassword, "admin@ncnhs.edu"]);
        echo "Admin user created successfully!\n";
    }
    
    echo "\n=== SETUP COMPLETE ===\n";
    echo "Database: $dbname\n";
    echo "Admin username: admin\n";
    echo "Admin password: admin123\n";
    echo "You can now login to the system.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

<?php
echo "Setting up NCNHS database with all required tables...\n";

$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default
$dbname = "ncnhsdb";

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$servername", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✓ Database '$dbname' ready\n";
    
    // Connect to specific database
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create admin_users table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✓ admin_users table ready\n";
    
    // Create events table
    $sql = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        location VARCHAR(255) NOT NULL,
        event_type VARCHAR(50) NOT NULL DEFAULT 'event',
        organizer VARCHAR(255) NOT NULL,
        announcement_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✓ events table ready\n";
    
    // Create about table
    $sql = "CREATE TABLE IF NOT EXISTS about (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_name VARCHAR(100) NOT NULL UNIQUE,
        Info TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✓ about table ready\n";
    
    // Insert or update admin user
    $adminUsername = "admin";
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$adminUsername]);
    
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
        $stmt->execute([$adminPassword, $adminUsername]);
        echo "✓ Admin user password updated\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$adminUsername, $adminPassword, "admin@ncnhs.edu"]);
        echo "✓ Admin user created\n";
    }
    
    // Insert sample events if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $eventCount = $stmt->fetchColumn();
    
    if ($eventCount == 0) {
        $sampleEvents = [
            [
                'title' => 'Faculty Meeting',
                'description' => 'Monthly faculty meeting to discuss curriculum updates',
                'event_date' => '2025-08-15',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'location' => 'Conference Room',
                'event_type' => 'meeting',
                'organizer' => 'Principal Office'
            ],
            [
                'title' => 'Science Fair',
                'description' => 'Annual school science fair exhibition',
                'event_date' => '2025-08-20',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'location' => 'Main Gymnasium',
                'event_type' => 'academic',
                'organizer' => 'Science Department'
            ],
            [
                'title' => 'School Holiday',
                'description' => 'National Heroes Day',
                'event_date' => '2025-08-26',
                'start_time' => '00:00:00',
                'end_time' => '23:59:59',
                'location' => 'School Wide',
                'event_type' => 'holiday',
                'organizer' => 'Administration'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, location, event_type, organizer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($sampleEvents as $event) {
            $stmt->execute([
                $event['title'],
                $event['description'],
                $event['event_date'],
                $event['start_time'],
                $event['end_time'],
                $event['location'],
                $event['event_type'],
                $event['organizer']
            ]);
        }
        echo "✓ Sample events inserted\n";
    } else {
        echo "✓ Events table has $eventCount existing events\n";
    }
    
    echo "\n=== SETUP COMPLETE ===\n";
    echo "Database: $dbname\n";
    echo "Login credentials:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "\nTest the setup by visiting your login page.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ncnhsdb"; // Using the main database

try {
    // Create connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully to database: $dbname\n";
    
    // Check if announcements table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'announcements'");
    if ($checkTable->rowCount() == 0) {
        echo "Creating announcements table...\n";
        
        $createTable = "
            CREATE TABLE announcements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,
                display_date VARCHAR(100) NOT NULL,
                category VARCHAR(50) DEFAULT 'general',
                priority VARCHAR(20) DEFAULT 'normal',
                attachment VARCHAR(255) NULL,
                image_filename VARCHAR(255) NULL,
                author VARCHAR(100) DEFAULT 'Administrator',
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($createTable);
        echo "Announcements table created successfully.\n";
    } else {
        echo "Announcements table already exists.\n";
    }
    
    // Check current announcements count
    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM announcements WHERE is_active = 1");
    $currentCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Current active announcements: $currentCount\n";
    
    if ($currentCount == 0) {
        echo "Adding sample announcements...\n";
        
        $sampleAnnouncements = [
            [
                'title' => 'Welcome to School Year 2024-2025',
                'content' => 'We warmly welcome all students, faculty, and staff to the new academic year 2024-2025. Let us work together to achieve excellence in education and create meaningful learning experiences for everyone.',
                'display_date' => 'August 2024',
                'category' => 'general',
                'priority' => 'high'
            ],
            [
                'title' => 'Class Schedule and Room Assignments',
                'content' => 'All students are advised to check their class schedules and room assignments posted on the school bulletin board. Classes will begin on August 15, 2024. Please report to your assigned classrooms on time.',
                'display_date' => 'August 2024',
                'category' => 'academic',
                'priority' => 'high'
            ],
            [
                'title' => 'Parent-Teacher Conference',
                'content' => 'We cordially invite all parents and guardians to attend the Parent-Teacher Conference scheduled for September 2024. This is an excellent opportunity to discuss your child\'s academic progress and development.',
                'display_date' => 'September 2024',
                'category' => 'event',
                'priority' => 'normal'
            ],
            [
                'title' => 'Sports Week 2024',
                'content' => 'Join us for our annual Sports Week featuring various athletic competitions including basketball, volleyball, track and field, and more. Registration is now open for all interested students.',
                'display_date' => 'October 2024',
                'category' => 'sports',
                'priority' => 'normal'
            ],
            [
                'title' => 'School Library Hours Extended',
                'content' => 'Good news! Our school library will now be open from 7:00 AM to 6:00 PM, Monday through Friday. We encourage all students to take advantage of these extended hours for study and research.',
                'display_date' => 'August 2024',
                'category' => 'facility',
                'priority' => 'low'
            ]
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO announcements (title, content, display_date, category, priority, author, is_active) 
            VALUES (?, ?, ?, ?, ?, 'Administrator', 1)
        ");
        
        foreach ($sampleAnnouncements as $announcement) {
            $insertStmt->execute([
                $announcement['title'],
                $announcement['content'],
                $announcement['display_date'],
                $announcement['category'],
                $announcement['priority']
            ]);
        }
        
        echo "Added " . count($sampleAnnouncements) . " sample announcements.\n";
    } else {
        echo "Announcements already exist, skipping sample data insertion.\n";
    }
    
    // Verify the data
    $verifyStmt = $pdo->query("SELECT id, title, is_active FROM announcements ORDER BY created_at DESC");
    $allAnnouncements = $verifyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nAll announcements in database:\n";
    foreach ($allAnnouncements as $ann) {
        echo "ID: {$ann['id']}, Title: {$ann['title']}, Active: {$ann['is_active']}\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

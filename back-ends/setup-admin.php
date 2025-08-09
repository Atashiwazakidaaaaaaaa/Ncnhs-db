<?php
$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default password
$dbname = "ncnhsdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Admin credentials
    $adminUsername = "admin";
    $adminPassword = '$2y$10$QIyrBiFxydIfeJzHUYsqIelxjoaihXALWhOJrd3tylI8BGGC/mFVW'; // Pre-hashed password for 'securepass_0b11011'
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT * FROM usercreds WHERE username = ?");
    $stmt->execute([$adminUsername]);
    
    if ($stmt->rowCount() > 0) {
        // Update existing admin user
        $stmt = $pdo->prepare("UPDATE usercreds SET password = ? WHERE username = ?");
        $stmt->execute([$adminPassword, $adminUsername]);
        echo "Admin user password updated successfully!" . PHP_EOL;
    } else {
        // Insert new admin user
        $stmt = $pdo->prepare("INSERT INTO usercreds (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$adminUsername, $adminPassword, "admin@ncnhs.edu"]);
        echo "Admin user created successfully!" . PHP_EOL;
    }
    
    // Verify the user was created/updated
    $stmt = $pdo->prepare("SELECT * FROM usercreds WHERE username = ?");
    $stmt->execute([$adminUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Admin user details:" . PHP_EOL;
        echo "ID: " . $user['id'] . PHP_EOL;
        echo "Username: " . $user['username'] . PHP_EOL;
        echo "Email: " . $user['email'] . PHP_EOL;
        echo "Password hash: " . $user['password'] . PHP_EOL;
        
        // Test password verification
        $testPassword = 'securepass_0b11011';
        if (password_verify($testPassword, $user['password'])) {
            echo "Password verification: SUCCESS" . PHP_EOL;
        } else {
            echo "Password verification: FAILED" . PHP_EOL;
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
?>

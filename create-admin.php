<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default password
$dbname = "ncnhsdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully\n";
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create admin user
$adminUsername = "admin";
$adminPassword = "securepass_0b11011";
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// Check if user already exists
$stmt = $pdo->prepare("SELECT * FROM usercreds WHERE username = ?");
$stmt->execute([$adminUsername]);
$existingUser = $stmt->fetch();

if ($existingUser) {
    // Update existing user
    $stmt = $pdo->prepare("UPDATE usercreds SET password = ? WHERE username = ?");
    $stmt->execute([$hashedPassword, $adminUsername]);
    echo "Admin user updated successfully\n";
} else {
    // Create new user
    $stmt = $pdo->prepare("INSERT INTO usercreds (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$adminUsername, $hashedPassword, "admin@ncnhs.edu"]);
    echo "Admin user created successfully\n";
}

// Verify the user was created/updated
$stmt = $pdo->prepare("SELECT * FROM usercreds WHERE username = ?");
$stmt->execute([$adminUsername]);
$user = $stmt->fetch();

if ($user) {
    echo "User verification:\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Password hash: " . $user['password'] . "\n";
    
    // Test password verification
    if (password_verify($adminPassword, $user['password'])) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
    }
} else {
    echo "Error: User not found after creation\n";
}

?>

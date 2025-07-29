<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default password
$dbname = "ncnhsdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully<br>";
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if usercreds table exists
$stmt = $pdo->prepare("SHOW TABLES LIKE 'usercreds'");
$stmt->execute();
$tableExists = $stmt->fetch();

if (!$tableExists) {
    echo "usercreds table does not exist. Creating it...<br>";
    $createTable = "CREATE TABLE usercreds (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTable);
    echo "usercreds table created successfully<br>";
} else {
    echo "usercreds table exists<br>";
}

// Show table structure
echo "<h3>Table Structure:</h3>";
$stmt = $pdo->prepare("DESCRIBE usercreds");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($columns);
echo "</pre>";

// Show current users
echo "<h3>Current Users:</h3>";
$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM usercreds");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($users);
echo "</pre>";

// Create/Update admin user
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
    echo "Admin user updated successfully<br>";
} else {
    // Create new user
    $stmt = $pdo->prepare("INSERT INTO usercreds (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$adminUsername, $hashedPassword, "admin@ncnhs.edu"]);
    echo "Admin user created successfully<br>";
}

// Verify the user was created/updated
$stmt = $pdo->prepare("SELECT * FROM usercreds WHERE username = ?");
$stmt->execute([$adminUsername]);
$user = $stmt->fetch();

if ($user) {
    echo "<h3>User Verification:</h3>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Password hash: " . $user['password'] . "<br>";
    
    // Test password verification
    if (password_verify($adminPassword, $user['password'])) {
        echo "Password verification: <strong>SUCCESS</strong><br>";
    } else {
        echo "Password verification: <strong>FAILED</strong><br>";
    }
} else {
    echo "Error: User not found after creation<br>";
}

// Final users list
echo "<h3>Final Users List:</h3>";
$stmt = $pdo->prepare("SELECT id, username, email, created_at FROM usercreds");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($users);
echo "</pre>";
?>

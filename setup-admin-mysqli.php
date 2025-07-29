<?php
$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default password
$dbname = "ncnhsdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin credentials
$adminUsername = "admin";
$adminPassword = '$2y$10$QIyrBiFxydIfeJzHUYsqIelxjoaihXALWhOJrd3tylI8BGGC/mFVW'; // Pre-hashed password for 'securepass_0b11011'

// Check if admin user already exists
$stmt = $conn->prepare("SELECT * FROM usercreds WHERE username = ?");
$stmt->bind_param("s", $adminUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing admin user
    $stmt = $conn->prepare("UPDATE usercreds SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $adminPassword, $adminUsername);
    $stmt->execute();
    echo "Admin user password updated successfully!" . PHP_EOL;
} else {
    // Insert new admin user
    $stmt = $conn->prepare("INSERT INTO usercreds (username, password, email) VALUES (?, ?, ?)");
    $email = "admin@ncnhs.edu";
    $stmt->bind_param("sss", $adminUsername, $adminPassword, $email);
    $stmt->execute();
    echo "Admin user created successfully!" . PHP_EOL;
}

// Verify the user was created/updated
$stmt = $conn->prepare("SELECT * FROM usercreds WHERE username = ?");
$stmt->bind_param("s", $adminUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
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

$conn->close();
?>

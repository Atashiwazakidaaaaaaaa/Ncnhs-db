<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ncnhsdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query all users
$sql = "SELECT id, username, password, email FROM usercreds";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>User Credentials in Database:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Password Hash</th><th>Email</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["username"] . "</td>";
        echo "<td style='font-family: monospace; font-size: 12px;'>" . $row["password"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found in database.";
}

$conn->close();
?>

<style>
table { border-collapse: collapse; width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>

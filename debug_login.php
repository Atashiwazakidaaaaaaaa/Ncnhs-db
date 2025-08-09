<?php
echo "<h2>Testing login.php</h2>";

// Test 1: Direct database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once 'config.php';
    if ($pdo) {
        echo "✓ Database connection successful<br>";
        
        // Test if admin_users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
        if ($stmt->rowCount() > 0) {
            echo "✓ admin_users table exists<br>";
            
            // Check if admin user exists
            $stmt = $pdo->prepare("SELECT id, username, email FROM admin_users WHERE username = ?");
            $stmt->execute(['admin']);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "✓ Admin user found: " . json_encode($user) . "<br>";
            } else {
                echo "❌ Admin user not found<br>";
            }
        } else {
            echo "❌ admin_users table does not exist<br>";
        }
    } else {
        echo "❌ Database connection failed: " . ($db_error ?? 'Unknown error') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 2: Simulate POST request
echo "<h3>2. Login POST Simulation</h3>";
$_POST['username'] = 'admin';
$_POST['password'] = 'admin123';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Simulating POST with username: admin, password: admin123<br>";

ob_start();
try {
    include 'login.php';
} catch (Exception $e) {
    echo "❌ Exception in login.php: " . $e->getMessage() . "<br>";
}
$output = ob_get_clean();

echo "<h3>3. Login Response</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Test 3: Manual login verification
echo "<h3>4. Manual Password Verification</h3>";
if (isset($pdo)) {
    $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $storedHash = $row['password'];
        $testPassword = 'admin123';
        $verified = password_verify($testPassword, $storedHash);
        
        echo "Stored hash: " . substr($storedHash, 0, 50) . "...<br>";
        echo "Test password: $testPassword<br>";
        echo "Verification result: " . ($verified ? "✓ VALID" : "❌ INVALID") . "<br>";
    }
}
?>

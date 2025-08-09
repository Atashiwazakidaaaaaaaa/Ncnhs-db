<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', 'root');
    $result = $pdo->query('SHOW DATABASES');
    echo "Available databases:\n";
    while ($row = $result->fetch()) {
        echo "- " . $row[0] . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

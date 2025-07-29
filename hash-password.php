<?php
$password = 'securepass_0b11011';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashedPassword . PHP_EOL;
?>

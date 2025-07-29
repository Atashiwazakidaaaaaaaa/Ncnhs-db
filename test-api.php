<?php
// Test the API endpoint
$url = 'http://localhost:80/test-login.php';
$data = json_encode([
    'username' => 'admin',
    'password' => 'securepass_0b11011'
]);

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "API Response: " . $result . PHP_EOL;
?>

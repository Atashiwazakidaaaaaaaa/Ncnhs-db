<?php
echo "Current directory: " . getcwd() . PHP_EOL;
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . PHP_EOL;
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not set') . PHP_EOL;
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . PHP_EOL;
phpinfo();
?>

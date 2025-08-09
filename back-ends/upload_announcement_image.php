<?php
// Allow CORS for dev
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$targetDir = __DIR__ . '/../static/uploads/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$response = ["success" => false, "message" => "", "filename" => null];

if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Upload error.';
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $response['message'] = 'Invalid file type.';
    } elseif ($file['size'] > $maxSize) {
        $response['message'] = 'File too large.';
    } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('announcement_', true) . '.' . $ext;
        $targetFile = $targetDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $response['success'] = true;
            $response['filename'] = 'uploads/' . $filename;
        } else {
            $response['message'] = 'Failed to save file.';
        }
    }
} else {
    $response['message'] = 'No file uploaded.';
}

header('Content-Type: application/json');
echo json_encode($response);

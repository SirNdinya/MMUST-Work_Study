<?php

define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// Define allowed file extensions
$allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

function reArrayFiles(&$file_post): array {
    $file_array = [];
    $file_count = count($file_post['name'] ?? []);
    $file_keys = array_keys($file_post);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_array[$i][$key] = $file_post[$key][$i] ?? null;
        }
    }

    return $file_array;
}
function processUpload(array $file): array {
    $max_size = MAX_FILE_SIZE;

    // Ensure required keys exist in file array
    if (!isset($file['tmp_name'], $file['name'], $file['size'], $file['error'])) {
        throw new Exception('Invalid file upload structure');
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error. Error code: " . $file['error']);
    }

    if ($file['size'] > $max_size) {
        throw new Exception("File size exceeds maximum allowed (50MB). File size: " . $file['size']);
    }

    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

    // Ensure the file extension is in the allowed list
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception("Invalid file extension. Allowed types: " . implode(', ', $allowedExtensions));
    }

    // Read the file content
    $fileContent = file_get_contents($file['tmp_name']);
    if ($fileContent === false) {
        throw new Exception("Failed to read uploaded file content");
    }

    return [
        'original_name' => basename($file['name']),
        'file_type' => $extension,
        'file_size' => $file['size'],
        'file_content' => $fileContent
    ];
}
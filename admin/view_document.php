<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$docId = (int)$_GET['id'];

// Get document data
$stmt = $conn->prepare("SELECT * FROM application_docs WHERE id = ?");
$stmt->bind_param("i", $docId);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    header('Location: dashboard.php');
    exit;
}

// Determine the appropriate Content-Type based on file extension
$extension = strtolower(pathinfo($document['original_name'], PATHINFO_EXTENSION));

$mimeTypes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

// Set headers for inline display
header("Content-Type: $contentType");
header("Content-Disposition: inline; filename=\"" . $document['original_name'] . "\"");
header("Content-Length: " . strlen($document['file_content']));

// Output the file content
echo $document['file_content'];
exit;
?>
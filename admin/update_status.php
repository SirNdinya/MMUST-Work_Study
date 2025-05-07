<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_id = $_POST['applicant_id'] ?? null;
    $new_status = $_POST['new_status'] ?? null;

    if ($applicant_id && in_array($new_status, ['Approved', 'Rejected'])) {
        // Check current status first
        $check = $conn->prepare("SELECT status FROM applications WHERE id = ?");
        $check->bind_param("i", $applicant_id);
        $check->execute();
        $check_result = $check->get_result();
        $row = $check_result->fetch_assoc();

        if ($row && $row['status'] === $new_status) {
            // No update needed
            http_response_code(204); // No Content
            exit;
        }

        // Perform update if status is different
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $applicant_id);
        if ($stmt->execute()) {
            http_response_code(200);
            exit;
        }
    }
}

http_response_code(400);

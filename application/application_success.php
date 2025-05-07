<?php
require_once __DIR__. '/../config/config.php';

session_start();


unset($_SESSION['app_ref']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Application Submitted</title>
    <link rel="stylesheet" href="../assets/css/application.css">
</head>
<body>
    <div class="container">
        <div class="success-message">
            <div class="success-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <h1>Application Submitted Successfully!</h1>
            <p>Your work-study application has been received and is under review.</p>
            <a href="../pages/student_dashboard.php" class="btn btn-primary">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>
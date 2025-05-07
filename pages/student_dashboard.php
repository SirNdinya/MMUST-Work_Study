<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/config.php';
session_start();

// Step 1: Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

// Step 2: Retrieve reg_number from session
$reg_number = $_SESSION['reg_number'] ?? null;


// Step 3: Initialize display variables
$studentName = 'Student';
$status = 'Not Applied';
$notification = $_SESSION['notification'] ?? '';

// Step 4: Get user data from 'users' table
$query = "SELECT username, application_status FROM users WHERE reg_number = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("s", $reg_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $studentName = $row['username'] ?? $studentName;
        $status = $row['application_status'] ?? $status;
        $_SESSION['application_status'] = $status;
    } else {
        die("Error: No user found for reg_number = '$reg_number'.");
    }

    $stmt->close();
} else {
    die("Database Error: Failed to prepare statement: " . $conn->error);
}

// Step 5: Check actual status from 'applications' table
$appStatusQuery = "SELECT status FROM applications WHERE reg_number = ? LIMIT 1";
$appStmt = $conn->prepare($appStatusQuery);

if ($appStmt) {
    $appStmt->bind_param("s", $reg_number);
    $appStmt->execute();
    $result = $appStmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $actualStatus = $row['status']; 

        if (strtolower($status) !== strtolower($actualStatus)) {
            // Sync users table to match applications table
            $updateQuery = "UPDATE users SET application_status = ? WHERE reg_number = ?";
            $updateStmt = $conn->prepare($updateQuery);

            if ($updateStmt) {
                $updateStmt->bind_param("ss", $actualStatus, $reg_number);
                $updateStmt->execute();
                $updateStmt->close();
            }

            // Update session and local variable
            $status = $actualStatus;
            $_SESSION['application_status'] = $status;
        }
    }

    $appStmt->close();
} else {
    die("Database Error: Failed to prepare status check: " . $conn->error);
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="http://localhost/work_study/assets/css/student_dashboard.css">
    <style>
        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        header h1 {
            color: #1a73e8;
            font-size: 2em;
        }
        nav ul {
            list-style: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin-right: 20px;
        }
        nav ul li a {
            text-decoration: none;
            color: #1a73e8;
            font-weight: bold;
        }
        .status-panel, .action-panel, .notification-panel {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        .status-panel h2, .action-panel h2, .notification-panel h2 {
            font-size: 1.5em;
            color: #333;
        }
        .status {
            font-size: 1.2em;
            font-weight: bold;
            padding: 10px;
            border-radius: 4px;
            color: white;
        }
        .status.not-applied { background-color: #ff6347; } 
        .status.already-applied { background-color: #ffa500; }
        .status.accepted { background-color: #28a745; }
        .status.rejected { background-color: #dc3545; }
        .notification-panel {
            background-color: #e9f7f9;
            border-left: 5px solid #1a73e8;
        }
        .quick-actions button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .quick-actions button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($studentName); ?>!</h1>
            <nav>
                <ul>
                    <li><a href="../pages/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <!-- Application Status Panel -->
            <section class="status-panel">
                <h2>Application Status</h2>
                <p class="status <?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                    <?php echo htmlspecialchars($status); ?>
                </p>
            </section>

            <?php if (strtolower($status) === 'not applied'): ?>
    <!-- Quick Actions Panel for 'Not Applied' students -->
    <section class="action-panel quick-actions">
        <h2>Quick Actions</h2>
        <button onclick="location.href='../application/application.php'">Apply for Work Study</button>
    </section>

<?php elseif (strtolower($status) === 'pending'): ?>
    <!-- Status: Pending -->
    <section class="status-panel pending">
        <h2>Application Status: Pending</h2>
        <p>Your application has been received and is currently under review.</p>
    </section>

<?php elseif (strtolower($status) === 'approved'): ?>
    <!-- Status: Approved -->
    <section class="status-panel approved">
        <h2>Application Status: Approved </h2>
        <p>Congratulations! You’ve been accepted into the work-study program.</p>
    </section>

<?php elseif (strtolower($status) === 'rejected'): ?>
    <!-- Status: Rejected -->
    <section class="status-panel rejected">
        <h2>Application Status: Rejected </h2>
        <p>Unfortunately, your application was not successful. You may contact the admin for more info.</p>
    </section>

<?php else: ?>
    <!-- Fallback -->
    <section class="status-panel unknown">
        <h2>Application Status: <?= htmlspecialchars($status) ?></h2>
        <p>We're not sure what this status means. Please contact support if this seems incorrect.</p>
    </section>
<?php endif; ?>


            <!-- Notifications Panel -->
            <section class="notification-panel">
                <h2>Notifications</h2>
                <?php
                        $reg_number = $_SESSION['reg_number']; // Ensure this is set when student logs in
                        $result = $conn->query("SELECT message, timestamp FROM notifications WHERE reg_number = '$reg_number' ORDER BY timestamp DESC");

                        echo "<section class='notification-panel'><h2>Notifications</h2>";
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<p><strong>{$row['timestamp']}:</strong> " . htmlspecialchars($row['message']) . "</p>";
                            }
                        } else {
                            echo "<p>No notifications yet.</p>";
                        }
                        echo "</section>";
                        ?>

            </section>
        </main>
    </div>

    <script src="http://localhost/work_study/assets/js/student_dashboard.js"></script>
</body>
</html>

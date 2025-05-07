<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Updated SQL query to include total_required and remaining_to_be_accepted
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM applications) AS total_applications,
        (SELECT COUNT(*) FROM applications WHERE status = 'Pending') AS pending,
        (SELECT COUNT(*) FROM applications WHERE status = 'Approved') AS approved,
        (SELECT COUNT(*) FROM applications WHERE status = 'Rejected') AS rejected,
        (SELECT SUM(required_students) FROM departments) AS total_required,
        (
            (SELECT SUM(required_students) FROM departments) - 
            (SELECT COUNT(*) FROM applications WHERE status = 'Approved')
        ) AS remaining_to_be_accepted
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="applicants.php">Applications</a></li>
                    <li><a href="departments.php">Departments</a></li>
                    <li><a href="assignments.php">Assignments</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Applications</h3>
                    <p><?= $stats['total_applications'] ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Required</h3>
                    <p><?= $stats['total_required'] ?? 0 ?></p>
                </div>
                <div class="stat-card">
                    <h3>Remaining to be Accepted</h3>
                    <p><?= $stats['remaining_to_be_accepted'] ?? 0 ?></p>
                </div>
                <div class="stat-card pending">
                    <h3>Pending</h3>
                    <p><?= $stats['pending'] ?></p>
                </div>
                <div class="stat-card approved">
                    <h3>Approved</h3>
                    <p><?= $stats['approved'] ?></p>
                </div>
                <div class="stat-card rejected">
                    <h3>Rejected</h3>
                    <p><?= $stats['rejected'] ?></p>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Recent Applications</h2>
                <?php
                $recent = $conn->query("
                    SELECT full_name, reg_number, status 
                    FROM applications 
                    ORDER BY created_at DESC 
                    LIMIT 50
                ");
                if ($recent->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Reg No</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $recent->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['reg_number']) ?></td>
                                <td><span class="status-badge <?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent applications found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

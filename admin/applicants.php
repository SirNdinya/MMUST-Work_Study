<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch all applicants from the database
$stmt = $conn->prepare("SELECT * FROM applications ");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applications</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>All Applications</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="departments.php">Departments</a></li>
                    <li><a href="assignments.php">Assignments</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>List of Applicants</h2>

            <!-- Applications Table -->
            <table class="applications-table">
                <thead>
                    <tr>
                        <th>Reg. Number</th>
                        <th>Full Name</th>
                        <th>Status</th>
                        <th class="actions-column">Application Form</th>
                        <th class="actions-column">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['reg_number']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td class="actions-column">
                            <a href="application_details.php?id=<?= $row['id'] ?>" class="view-button button-small" style="padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; font-weight: bold; border-radius: 4px; box-shadow: 0 4px 6px rgba(0, 123, 255, 0.3); transition: all 0.3s ease-in-out;">
    View Form
</a>

                            </td>
                            <td class="actions-column">
                                <form class="status-form" data-id="<?= $row['id'] ?>">
                                    <select name="new_status">
                                        <option disabled selected>Change Status</option>
                                        <option value="Approved">Accept</option>
                                        <option value="Rejected">Reject</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- AJAX Script to handle status change -->
    <script>
    document.querySelectorAll('.status-form select').forEach(select => {
        select.addEventListener('change', function () {
            const form = this.closest('.status-form');
            const applicantId = form.getAttribute('data-id');
            const newStatus = this.value;

            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    applicant_id: applicantId,
                    new_status: newStatus
                })
            })
            .then(response => {
                if (response.ok) {
                    // Reload the page to reflect updated status
                    window.location.reload();
                } else {
                    alert('Failed to update status.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred while updating status.');
            });
        });
    });
    </script>
</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Get all departments
$departments = $conn->query("SELECT * FROM departments");

$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $department_id = $_POST['department_id'];
    $supervisor = $_POST['supervisor'];
    $required_students = $_POST['required_students'];

    // Update department supervisor and required students
    $stmt = $conn->prepare("UPDATE departments SET supervisor = ?, required_students = ? WHERE department_id = ?");
    $stmt->bind_param('ssi', $supervisor, $required_students, $department_id);

    if ($stmt->execute()) {
        // Set the feedback message
        $feedback = 'Department updated successfully!';

        // Redirect to the same page to refresh and show updated data
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $feedback = 'Failed to update department. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments</title>
    <link rel="stylesheet" href="../assets/css/departments.css">
</head>
<body style="background-color: #8d837f;">
    <!-- Header Section -->
    <header class="page-header">
        <h1>Departments</h1>
        <a href="dashboard.php" class="back-to-dashboard">Back to Dashboard</a>
    </header>

    <?php if ($feedback): ?>
        <div class="feedback"><?= $feedback ?></div>
    <?php endif; ?>

    <table class="department-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Supervisor</th>
                <th>Required Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $departments->fetch_assoc()): ?>
                <tr>
                    <form method="POST">
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td>
                            <input type="text" name="supervisor" value="<?= htmlspecialchars($row['supervisor']) ?>" required>
                        </td>
                        <td>
                            <input type="number" name="required_students" value="<?= $row['required_students'] ?>" required>
                        </td>
                        <td>
                            <input type="hidden" name="department_id" value="<?= $row['department_id'] ?>">
                            <button type="submit" name="update" class="update-button">Update</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

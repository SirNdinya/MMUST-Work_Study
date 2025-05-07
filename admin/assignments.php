<?php
session_start();
require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Get applicants with status "Approved" and their current assignment
$acceptedApplicants = $conn->query("
    SELECT 
        a.id, 
        a.full_name, 
        a.reg_number, 
        d.name AS department_name,
        ass.department_id
    FROM applications a
    LEFT JOIN assignments ass ON ass.applicant_id = a.id
    LEFT JOIN departments d ON d.department_id = ass.department_id
    WHERE a.status = 'Approved'
");

if (!$acceptedApplicants) {
    die("Error fetching applicants: " . $conn->error);
}

$feedback = '';

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $applicant_id = intval($_POST['applicant_id']);
    $department_id = intval($_POST['department_id']);

    // Check if applicant is already assigned
    $alreadyAssigned = $conn->query("SELECT * FROM assignments WHERE applicant_id = $applicant_id");
    if ($alreadyAssigned && $alreadyAssigned->num_rows > 0) {
        $feedback = ' Applicant already assigned.';
    } else {
        // Get department info
        $deptResult = $conn->query("SELECT * FROM departments WHERE department_id = $department_id");
        if (!$deptResult) {
            die("Department query failed: " . $conn->error);
        }
        $department = $deptResult->fetch_assoc();

        // Count current assignments in this department
        $countResult = $conn->query("SELECT COUNT(*) FROM assignments WHERE department_id = $department_id");
        if (!$countResult) {
            die("Count query failed: " . $conn->error);
        }
        $current_students = $countResult->fetch_row()[0];

        // Check capacity
        if ($current_students < $department['required_students']) {
            // Assign the applicant
            $stmt = $conn->prepare("INSERT INTO assignments (applicant_id, department_id, status) VALUES (?, ?, 'Assigned')");
            $stmt->bind_param('ii', $applicant_id, $department_id);

            if ($stmt->execute()) {
                $feedback = ' Applicant assigned successfully.';

                // Get applicant info
                $applicantResult = $conn->query("SELECT full_name, reg_number FROM applications WHERE id = $applicant_id");
                $applicant = $applicantResult->fetch_assoc();

                $student_name = $applicant['full_name'];
                $reg_number = $applicant['reg_number'];
                $department_name = $department['name'];

                // Prepare notification insert (no timestamp)
                $stmt_notify = $conn->prepare("INSERT INTO notifications (reg_number, message) VALUES (?, ?)");

                // Function to ensure reg_number fits within the limit
                function truncateRegNumber($reg_number) {
                    return substr($reg_number, 0, 20); // Truncate to 20 characters
                }

                // Truncate for consistency
                $reg_number = truncateRegNumber($reg_number);

                // 1. Notify assigned student
                $message = "You have been assigned to the $department_name department.";
                $stmt_notify->bind_param('ss', $reg_number, $message);
                $stmt_notify->execute();

                // 2. Notify others already in that department
                $others = $conn->query("
                    SELECT a.reg_number, a.full_name 
                    FROM assignments ass 
                    JOIN applications a ON a.id = ass.applicant_id 
                    WHERE ass.department_id = $department_id AND a.id != $applicant_id
                ");

                if ($others && $others->num_rows > 0) {
                    while ($row = $others->fetch_assoc()) {
                        $other_reg = truncateRegNumber($row['reg_number']);
                        $other_name = $row['full_name'];

                        // Notify other students about the new assignee
                        $message_to_others = "$student_name has joined your department ($department_name). You are now colleagues.";
                        $stmt_notify->bind_param('ss', $other_reg, $message_to_others);
                        $stmt_notify->execute();

                        // Notify new student about each existing student
                        $message_to_new = "$other_name is already in the $department_name department. You are colleagues.";
                        $stmt_notify->bind_param('ss', $reg_number, $message_to_new);
                        $stmt_notify->execute();
                    }
                }

                $stmt_notify->close();

            } else {
                $feedback = ' Failed to assign: ' . $stmt->error;
            }

            $stmt->close();
        } else {
            $feedback = ' Department is full.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #8d837f;
        }
        nav {
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav h1 {
            margin: 0;
            font-size: 1.5em;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .feedback {
            margin: 10px 20px;
            padding: 10px;
            background: #eaeaea;
            border-left: 5px solid #007BFF;
        }
        .assign-button {
            padding: 5px 10px;
        }
        .applicant-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px;
        }
        .applicant-table th,
        .applicant-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        select:disabled, button:disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<nav>
    <h1>Assign Accepted Applicants</h1>
    <a href="dashboard.php">← Back to Dashboard</a>
</nav>

<?php if ($feedback): ?>
    <div class="feedback"><?= htmlspecialchars($feedback) ?></div>
<?php endif; ?>

<table class="applicant-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Reg No</th>
            <th>Assigned Department</th>
            <th>Assign To</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $acceptedApplicants->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['reg_number']) ?></td>
                <td><?= htmlspecialchars($row['department_name'] ?? 'Not Assigned') ?></td>
                <td>
                    <?php if (!empty($row['department_name'])): ?>
                        <button class="assign-button" disabled>Assigned</button>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="applicant_id" value="<?= $row['id'] ?>">
                            <select name="department_id" required>
                                <?php
                                $departments = $conn->query("SELECT * FROM departments");
                                while ($dept = $departments->fetch_assoc()):
                                    $dept_id = $dept['department_id'];
                                    $dept_name = htmlspecialchars($dept['name']);

                                    // Check if department is full
                                    $assignedCount = $conn->query("SELECT COUNT(*) FROM assignments WHERE department_id = $dept_id")->fetch_row()[0];
                                    $isFull = $assignedCount >= $dept['required_students'];
                                ?>
                                    <option value="<?= $dept_id ?>" <?= $isFull ? 'disabled' : '' ?>>
                                        <?= $dept_name ?> <?= $isFull ? '(Full)' : '' ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" name="assign" class="assign-button">Assign</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>

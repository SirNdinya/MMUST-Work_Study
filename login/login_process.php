<?php

require '../config/config.php'; // Include your DB connection file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
};


header('Content-Type: application/json'); // We expect a JSON response
$response = ["status" => "error", "message" => "Unexpected error"]; // Default response

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_number = htmlspecialchars(trim($_POST['reg_number']), ENT_QUOTES, 'UTF-8');

    // Validate reg number existence
    if (isset($_POST["validate_reg_number"])) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE reg_number = ?");
        $stmt->bind_param("s", $reg_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $response = ["status" => "success", "username" => $row['username']];
        } else {
            $response = ["status" => "error", "message" => "Registration number not found"];
        }

        $stmt->close();
        echo json_encode($response);
        exit();
    }

    // Validate password and login
    if (isset($_POST['validate_password'])) {
        try {
            $password = trim($_POST["password"] ?? '');
            
            if (empty($reg_number) || empty($password)) {
                throw new Exception("Missing credentials");
            }
    
            // Updated query with correct column names
            $stmt = $conn->prepare("
            SELECT u.user_id, u.username, u.reg_number, u.application_status, u.role, u.password_hash,
                   CONCAT(s.first_name, ' ', s.surname) AS full_name
            FROM users u
            JOIN students s ON u.reg_number = s.reg_number
            WHERE u.reg_number = ?
        ");
                    if (!$stmt) {
                throw new Exception("Database prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("s", $reg_number);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Get result failed: " . $stmt->error);
            }
    
            if ($row = $result->fetch_assoc()) {
                // Verify against password_hash column
                if (password_verify($password, $row["password_hash"])) {
                    // When login is successful:
                $_SESSION['user_id'] = $row['user_id'];  
                $_SESSION['username'] = $row['username'];
                $_SESSION['reg_number'] = $row['reg_number'];
                $_SESSION['application_status'] = $row['application_status']; // <- Add this!
                $_SESSION['full_name'] = $row['full_name']; 
                $_SESSION['role'] = $row['role'];
                    $response = [
                        "status" => "success",
                        "message" => "Login Success",
                        "redirect" => "../pages/student_dashboard.php"
                    ];
                } else {
                    $response = ["status" => "error", "message" => "Incorrect Password"];
                }
            } else {
                $response = ["status" => "error", "message" => "Account not found"];
            }
            
            $stmt->close();
            echo json_encode($response);
            exit();
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $response = ["status" => "error", "message" => "System error: " . $e->getMessage()];
            echo json_encode($response);
            exit();
        }
    }
}

$conn->close();
echo json_encode($response);
?>



<?php

require '../config/config.php'; // Include database configuration

session_start();

header('Content-Type: application/json'); // Set response type to JSON

$response = ["status"=>"error", 'message'=> 'Error occurred'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_number = htmlspecialchars(trim($_POST['reg_number']) );
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);


     
    // Validate Registration Number
    if (isset($_POST['validate_reg_number'])) {
        $reg_number = trim($_POST['reg_number']);

        // Fetch student name
        $stmt = $conn->prepare("SELECT first_name, surname FROM students WHERE reg_number = ?");
        $stmt->bind_param("s", $reg_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $response = ["status" => "success", "full_name" => $row['first_name'] . " " . $row['surname']];
        } else {}
        $stmt->close();
        echo json_encode($response);
        exit(); // Stop further execution
    }

    // Check if the registration number exists in the students table
    $stmt = $conn->prepare("SELECT 1 FROM students WHERE reg_number = ?");
    $stmt->bind_param("s", $reg_number);
    $stmt->execute();
    $stmt->store_result();

    // If no record is found, return error
    if ($stmt->num_rows == 0) {
        $response = ["status" => "error", "message" => "Hello Invalid registration number."];
        echo json_encode($response);
        exit; // Exit after the response is sent
    }
    $stmt->close();

    // Password validation
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $response["message"] = "Create a strong password with characters, strings, capital and small letters.";
        echo json_encode($response);
        exit;
    }

    // Password match validation
    if ($password !== $confirm_password) {
        $response["message"] = "Hello Passwords do not match.";
        echo json_encode($response);
        exit;
    }

    // Check if reg_number already exists
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE reg_number = ?");
    $stmt->bind_param("s", $reg_number);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ["status" => "error", "message" => "Hello You are already registered."];
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Check if username already exists
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ["status" => "error", "message" => "Hello Username already taken."];
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (reg_number, username, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $reg_number, $username, $password_hash);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Hello Registration successful!"];
    } else {
        $response = ["status" => "error", "message" => "Error: " . $stmt->error];
    }

    $stmt->close();
}

$conn->close(); // Close database connection
echo json_encode($response); // Send JSON response
?>

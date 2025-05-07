<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_NAME', 'work_study');
define('DB_PASS', 'sir~ndinya');


// Set session parameters BEFORE starting session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    // Additional session security
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    
    if (isset($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', '1');
    }
}

// Initialize database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    $conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
} catch (mysqli_sql_exception $e) {
    error_log("DB Connection failed: " . $e->getMessage());
    http_response_code(503);
    exit('Service unavailable');
}
<?php
// Strict error reporting
declare(strict_types=1);

// Core PHP settings (must be before any output/session)
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '52M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Load config after core settings
require __DIR__ . '/config.php';

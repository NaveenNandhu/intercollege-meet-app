<?php
date_default_timezone_set('UTC');

// Show errors (turn OFF in production later)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
| If Railway environment variables exist → use them
| Otherwise → use local XAMPP config
*/

if (getenv('DB_HOST')) {
    // 🌍 Railway (Cloud)
    $host = getenv('DB_HOST');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');
    $db   = getenv('DB_NAME');
    $port = getenv('DB_PORT') ?: 3306;
} else {
    // 💻 Local (XAMPP)
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';
    $db   = 'intercollege_meet_app';
    $port = 3307;
}

// Create connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

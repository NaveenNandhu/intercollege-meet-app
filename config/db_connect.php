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
if (getenv('MYSQLHOST')) {

    define('DB_SERVER', getenv('MYSQLHOST'));
    define('DB_USERNAME', getenv('MYSQLUSER'));
    define('DB_PASSWORD', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
    define('DB_PORT', getenv('MYSQLPORT') ?: 3306);

} else {

    // Local XAMPP
    define('DB_SERVER', '127.0.0.1');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'intercollege_meet_app');
    define('DB_PORT', 3307);
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

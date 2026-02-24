<?php
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
| If running locally → use XAMPP values
| If running in cloud (Render) → use Environment Variables
*/

if (getenv('DB_HOST')) {
    // 🌍 CLOUD (Render / Production)
    define('DB_SERVER', getenv('DB_HOST'));
    define('DB_USERNAME', getenv('DB_USER'));
    define('DB_PASSWORD', getenv('DB_PASS'));
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_PORT', getenv('DB_PORT') ?: 3306);
} else {
    // 💻 LOCAL (XAMPP)
    define('DB_SERVER', '127.0.0.1');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'intercollege_meet_app');
    define('DB_PORT', 3307);
}

// --- Establish Connection ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// --- Start Session ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

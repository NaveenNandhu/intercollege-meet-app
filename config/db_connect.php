<?php
date_default_timezone_set('UTC');

/* 
|--------------------------------------------------------------------------
| DATABASE CONFIG
|--------------------------------------------------------------------------
| Railway uses MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, etc.
| XAMPP uses local credentials
*/

if (getenv('MYSQLHOST')) {

    // 🌍 RAILWAY (Production)
    define('DB_SERVER', getenv('MYSQLHOST'));
    define('DB_USERNAME', getenv('MYSQLUSER'));
    define('DB_PASSWORD', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
    define('DB_PORT', getenv('MYSQLPORT'));

} else {

    // 💻 LOCAL (XAMPP)
    define('DB_SERVER', '127.0.0.1');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'intercollege_meet_app');
    define('DB_PORT', 3307);
}

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

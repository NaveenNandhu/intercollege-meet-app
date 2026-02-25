<?php
date_default_timezone_set('UTC');

// If running on Railway
if (getenv('MYSQLHOST')) {

    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT');

} else {

    // Local XAMPP
    $host = "127.0.0.1";
    $user = "root";
    $pass = "";
    $db   = "intercollege_meet_app";
    $port = 3307;
}

$conn = new mysqli($host, $user, $pass, $db, (int)$port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

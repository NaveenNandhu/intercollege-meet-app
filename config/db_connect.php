<?php
date_default_timezone_set('UTC');

if (getenv('DATABASE_URL')) {

    $url = parse_url(getenv('DATABASE_URL'));

    $host = $url['host'];
    $user = $url['user'];
    $pass = $url['pass'];
    $db   = ltrim($url['path'], '/');
    $port = $url['port'];

} else {

    // Local XAMPP fallback
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

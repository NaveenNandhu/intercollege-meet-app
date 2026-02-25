<?php
date_default_timezone_set('UTC');

$url = getenv('DATABASE_URL');

if (!$url) {
    die("DATABASE_URL not found.");
}

$dbparts = parse_url($url);

$host = $dbparts['host'];
$user = $dbparts['user'];
$pass = $dbparts['pass'];
$db   = ltrim($dbparts['path'], '/');
$port = $dbparts['port'];

$conn = new mysqli($host, $user, $pass, $db, (int)$port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();
?>

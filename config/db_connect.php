<?php

$host = getenv("DB_HOST") ?: "127.0.0.1";
$port = getenv("DB_PORT") ?: "3307";
$user = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASS") ?: "";
$dbname = getenv("DB_NAME") ?: "intercollege_meet";

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

exit();

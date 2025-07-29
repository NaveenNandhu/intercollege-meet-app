<?php
echo "<h1>Database Connection Test</h1>";

// --- Connection Details ---
$server_ip   = '127.0.0.1';
$username    = 'root';
$password    = ''; // Default for XAMPP is empty
$db_name     = 'intercollege_meet_app';
$port        = 3307;

echo "<p>Attempting to connect to MySQL on <strong>" . $server_ip . ":" . $port . "</strong> with username <strong>'" . $username . "'</strong>...</p>";

// --- Attempt Connection ---
// The '@' symbol suppresses the default PHP warning so we can show our own custom message.
$mysqli = @new mysqli($server_ip, $username, $password, $db_name, $port);

// --- Check for Connection Errors ---
if ($mysqli->connect_error) {
    echo "<h2 style='color: red;'>CONNECTION FAILED.</h2>";
    echo "<p><strong>Error Details:</strong> " . $mysqli->connect_error . "</p>";
    echo "<hr>";
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Is the MySQL module in your XAMPP Control Panel running with a <strong>green background</strong>?</li>";
    echo "<li>Does the XAMPP Control Panel show that MySQL is using port <strong>" . $port . "</strong>?</li>";
    echo "<li>Is your computer's firewall (like Windows Defender) possibly blocking the connection? Try temporarily disabling it to test.</li>";
    echo "</ol>";
} else {
    echo "<h2 style='color: green;'>CONNECTION SUCCESSFUL!</h2>";
    echo "<p>PHP was able to connect to the MySQL database successfully.</p>";
    echo "<p>This means the issue might be with how the main project files are including the config file, but your server and credentials are correct.</p>";
    
    // Close the connection
    $mysqli->close();
}
?>
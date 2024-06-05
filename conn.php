<?php
// Database configuration
$host = 'localhost';
$dbname = 'fyp_system';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

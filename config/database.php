<?php
$host = 'localhost'; // or database server IP
$username = 'root';  // MySQL user
$password = '';      // MySQL password
$database = 'hotel'; // database name

// MySQL connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

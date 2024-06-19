<?php
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your MySQL password
$database = "healthcare";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "furniture_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// No charset setting needed as per your request
?>
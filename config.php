<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hr";

// Create connection

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connecting
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";

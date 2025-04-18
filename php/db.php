<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Database connection settings for Agride
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriride";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

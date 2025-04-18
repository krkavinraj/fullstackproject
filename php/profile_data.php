<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Get vehicle info
$vehicle = $conn->query("SELECT * FROM vehicles WHERE user_id = $user_id LIMIT 1")->fetch_assoc();

// Get rides given
$rides_given = [];
$result = $conn->query("SELECT * FROM rides WHERE user_id = $user_id ORDER BY ride_date DESC");
while ($row = $result->fetch_assoc()) { $rides_given[] = $row; }

// (Optional) Get rides taken if you have a rides_taken table or logic
//$rides_taken = ...

$conn->close();

echo json_encode([
    'user' => $user,
    'vehicle' => $vehicle,
    'rides_given' => $rides_given
]);
?>

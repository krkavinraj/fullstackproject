<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to book a ride.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$ride_id = isset($_POST['ride_id']) ? intval($_POST['ride_id']) : 0;

if ($ride_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ride.']);
    exit;
}

// Prevent duplicate bookings
$check = $conn->prepare("SELECT 1 FROM bookings WHERE user_id = ? AND ride_id = ?");
$check->bind_param("ii", $user_id, $ride_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already booked this ride.']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("INSERT INTO bookings (user_id, ride_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $ride_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Ride booked successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Booking failed.']);
}
$stmt->close();
$conn->close();
?>

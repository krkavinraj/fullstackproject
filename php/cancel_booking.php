<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel a booking.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$ride_id = isset($_POST['ride_id']) ? intval($_POST['ride_id']) : 0;

if ($ride_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ride.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ? AND ride_id = ?");
$stmt->bind_param("ii", $user_id, $ride_id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No booking found or cancellation failed.']);
}
$stmt->close();
$conn->close();
?>

<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to view your bookings.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Join bookings with rides and vehicles for full ride info
$sql = "SELECT rides.*, vehicles.type AS vehicle_type, vehicles.number AS vehicle_number, users.name AS driver_name, users.phone AS driver_contact
        FROM bookings
        JOIN rides ON bookings.ride_id = rides.id
        LEFT JOIN vehicles ON rides.vehicle_id = vehicles.id
        LEFT JOIN users ON rides.user_id = users.id
        WHERE bookings.user_id = ?
        ORDER BY bookings.booked_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_rides = [];
while ($row = $result->fetch_assoc()) {
    $booked_rides[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'booked_rides' => $booked_rides]);
?>

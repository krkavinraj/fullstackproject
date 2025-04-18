<?php
// get_ride.php: Returns details for a single ride by ID (JSON)
header('Content-Type: application/json');
include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid or missing ride ID.']);
    exit;
}
$ride_id = intval($_GET['id']);

// Fetch ride details
$stmt = $conn->prepare("SELECT rides.*, vehicles.type AS vehicle_type, users.name AS driver_name, users.phone AS driver_contact FROM rides
    LEFT JOIN vehicles ON rides.vehicle_id = vehicles.id
    LEFT JOIN users ON rides.user_id = users.id
    WHERE rides.id = ? LIMIT 1");
$stmt->bind_param('i', $ride_id);
$stmt->execute();
$result = $stmt->get_result();
$ride = $result->fetch_assoc();
$stmt->close();

if (!$ride) {
    echo json_encode(['error' => 'Ride not found.']);
    exit;
}

// Optionally, add description or image fields if your schema supports them
// $ride['description'] = $ride['description'] ?? '';
// $ride['image'] = $ride['image'] ?? '';

// Hide sensitive info if needed
unset($ride['user_id']);

echo json_encode($ride);

<?php
header('Content-Type: application/json');
include 'db.php';

// Prepare SQL query to fetch rides
$sql = "SELECT id, from_city, to_city, ride_date, capacity, vehicle_type, image_path FROM rides WHERE 1=1";
$params = [];
$types = '';

if (isset($_GET['from_city']) && !empty($_GET['from_city'])) {
    $sql .= " AND LOWER(from_city) = ?";
    $params[] = strtolower($_GET['from_city']);
    $types .= 's';
}
if (isset($_GET['to_city']) && !empty($_GET['to_city'])) {
    $sql .= " AND LOWER(to_city) = ?";
    $params[] = strtolower($_GET['to_city']);
    $types .= 's';
}
if (isset($_GET['ride_date']) && !empty($_GET['ride_date'])) {
    $sql .= " AND ride_date = ?";
    $params[] = $_GET['ride_date'];
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rides = [];
while ($row = $result->fetch_assoc()) {
    $rides[] = [
        'id' => $row['id'],
        'from_city' => $row['from_city'],
        'to_city' => $row['to_city'],
        'ride_date' => $row['ride_date'],
        'capacity' => $row['capacity'],
        'vehicle_type' => $row['vehicle_type'],
        'image' => $row['image_path']
    ];
}
$stmt->close();
$conn->close();
echo json_encode($rides);
?>

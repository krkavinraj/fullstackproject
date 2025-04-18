<?php
session_start(); // Start session to potentially get user ID later
header('Content-Type: application/json');
include 'db.php'; // Includes the $conn variable

// Basic validation (more robust validation needed in production)
if (isset($_POST['fromCity']) && !empty(trim($_POST['fromCity'])) &&
    isset($_POST['toCity']) && !empty(trim($_POST['toCity'])) &&
    isset($_POST['rideDate']) && !empty(trim($_POST['rideDate'])) &&
    isset($_POST['capacity']) && is_numeric($_POST['capacity'])) {

    // Sanitize inputs (using real_escape_string)
    $from_city = $conn->real_escape_string(trim($_POST['fromCity']));
    $to_city = $conn->real_escape_string(trim($_POST['toCity']));
    $ride_date = $conn->real_escape_string(trim($_POST['rideDate']));
    $capacity = (int)$_POST['capacity']; // Cast to integer

    // Placeholders - replace with actual data later
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if no user logged in
    $vehicle_type = 'Truck'; // Placeholder - Add to form if needed
    $image_path = 'img/default_ride.jpg'; // Placeholder image for new rides

    // Prepare SQL statement to prevent SQL injection
    // Ensure your `rides` table has these columns: user_id, from_city, to_city, ride_date, capacity, vehicle_type, image_path
    $stmt = $conn->prepare("INSERT INTO rides (user_id, from_city, to_city, ride_date, capacity, vehicle_type, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
        exit();
    }

    // Bind parameters (i=integer, s=string)
    // Order must match the VALUES(?, ?, ...) placeholders
    $stmt->bind_param("isssiss", $user_id, $from_city, $to_city, $ride_date, $capacity, $vehicle_type, $image_path);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Ride offered successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database execute error: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing input. Please fill all required fields.']);
}

$conn->close();
?>

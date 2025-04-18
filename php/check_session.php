<?php
session_start();
header('Content-Type: application/json');

$response = ['logged_in' => false];

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $response['logged_in'] = true;
    $response['user_name'] = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
    $response['user_id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

echo json_encode($response);
?>

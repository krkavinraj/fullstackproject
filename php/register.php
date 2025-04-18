<?php
include 'db.php'; // Include the database connection

$response = ['success' => false, 'message' => 'An error occurred.'];

// Basic validation (add more robust validation as needed)
if (isset($_POST['name']) && !empty(trim($_POST['name'])) &&
    isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) &&
    isset($_POST['password']) && !empty($_POST['password'])) {

    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    // Hash the password for security
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response['message'] = 'Email already registered.';
    } else {
        // Insert new user
        $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($insert_stmt === false) {
            $response['message'] = 'Database prepare error: ' . $conn->error;
        } else {
            $insert_stmt->bind_param("sss", $name, $email, $password_hash);
            if ($insert_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Registration successful! You can now log in.';
            } else {
                $response['message'] = 'Database execute error: ' . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
    }
    $check_stmt->close();

} else {
    $response['message'] = 'Invalid input. Please provide name, valid email, and password.';
}

$conn->close();

// Redirect back to register page with message (or handle via JS)
// For simplicity here, we'll just echo JSON and handle redirection/alert in JS if needed.
// If using form action directly, you'd redirect like this:
// header('Location: ../register.html?message=' . urlencode($response['message']));
// exit();

// Since the original form uses action="php/register.php", we'll display a simple message.
// A better approach is AJAX submission like the offer_ride form.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Status</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.6/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md text-center">
        <h1 class="text-2xl font-bold mb-4">Registration Status</h1>
        <p class="mb-4"><?php echo htmlspecialchars($response['message']); ?></p>
        <?php if ($response['success']): ?>
            <a href="../login.html" class="bg-blue-600 text-white px-4 py-2 rounded">Go to Login</a>
        <?php else: ?>
            <a href="../register.html" class="bg-gray-600 text-white px-4 py-2 rounded">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>

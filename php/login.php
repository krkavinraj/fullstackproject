<?php
session_start(); // Start the session
include 'db.php'; // Include the database connection

$response = ['success' => false, 'message' => 'Invalid login attempt.'];

// Basic validation
if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) &&
    isset($_POST['password']) && !empty($_POST['password'])) {

    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    // Prepare statement to find user by email
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    if ($stmt === false) {
        $response['message'] = 'Database prepare error: ' . $conn->error;
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, store user info in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['logged_in'] = true;

                $response['success'] = true;
                $response['message'] = 'Login successful!';
                // Redirect to home page after successful login
                header('Location: ../index.html');
                exit(); // Important to exit after redirect
            } else {
                $response['message'] = 'Incorrect password.';
            }
        } else {
            $response['message'] = 'Email not found.';
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid input. Please provide valid email and password.';
}

$conn->close();

// If login failed, redirect back to login page with an error message
// Or display the error message directly
// header('Location: ../login.html?error=' . urlencode($response['message']));
// exit();

// Display simple feedback page for failed login if not redirected
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Status</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.6/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md text-center">
        <h1 class="text-2xl font-bold mb-4">Login Failed</h1>
        <p class="mb-4 text-red-600"><?php echo htmlspecialchars($response['message']); ?></p>
        <a href="../login.html" class="bg-blue-600 text-white px-4 py-2 rounded">Try Again</a>
    </div>
</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validate form data
    if (!empty($username) && !empty($password) && !empty($role)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute SQL statement
        $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $hashed_password, $role);

        if ($stmt->execute()) {
            // Registration successful, redirect to login page
            header("Location: index.html");
            exit();
        } else {
            // Registration failed, display error
            $error_message = 'Registration failed: ' . $conn->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // If any field is empty, display error
        $error_message = 'All fields are required.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        .register-form h2 {
            margin-bottom: 20px;
        }

        .register-form label {
            display: block;
            margin-top: 10px;
            text-align: left;
        }

        .register-form input,
        .register-form select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            margin-top: 5px;
        }

        .register-form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }

        .register-form button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="register-form">
        <h2>Registration Form</h2>
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form id="registerForm" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="admin">Admin</option>
                <option value="student">Student</option>
                <option value="supervisor">Supervisor</option>
            </select><br>
            
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>

<?php
session_start();

require 'conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'];
    $password = $input['password'];

    // Ensure input values are received
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error (prepare): ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'student') {
                // Fetch student_id and intake_id from students table
                $stmt->close();
                $stmt = $conn->prepare('SELECT student_id, intake_id FROM students WHERE user_id = ?');
                if (!$stmt) {
                    echo json_encode(['success' => false, 'message' => 'Database error (prepare student): ' . $conn->error]);
                    exit;
                }
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $student = $result->fetch_assoc();
                    $_SESSION['student_id'] = $student['student_id'];
                    $_SESSION['intake_id'] = $student['intake_id'];
                    echo json_encode(['success' => true, 'role' => $user['role']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Student ID not found for user ID ' . $user['id']]);
                }
            } elseif ($user['role'] === 'admin' || $user['role'] === 'supervisor') {
                echo json_encode(['success' => true, 'role' => $user['role']]);
            } else {
                echo json_encode(['success' => true, 'role' => $user['role']]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>

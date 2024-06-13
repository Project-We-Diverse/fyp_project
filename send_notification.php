<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and role is set
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role'])) {
    die('Access denied. Please log in.');
}

// Check if the logged-in user is a supervisor
if ($_SESSION['role'] !== 'supervisor') {
    die('Access denied. You must be a supervisor to perform this action.');
}

// Require database connection
require 'conn.php';

// Ensure form data is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $project_id = $_POST['project_id'];
    $notification_message = htmlspecialchars($_POST['notification_message']);

    // Get current timestamp
    $created_at = date('Y-m-d H:i:s');

    // Insert notification into the notifications table
    $sql_insert = "INSERT INTO notifications (project_id, notification_message, created_at)
                   VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iss", $project_id, $notification_message, $created_at);

    if ($stmt_insert->execute()) {
        // Notification successfully inserted
        // Update the Notify column to 'Notified'
        $sql_update = "UPDATE notifications SET notified = 'notified' WHERE project_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $project_id);
        $stmt_update->execute();

        $_SESSION['notification_sent'] = true; // Store a flag in session for notification sent
        header('Location: supervisor_notification.php'); // Redirect to notification page
        exit();
    } else {
        // Error in SQL execution
        die('Failed to send notification. Please try again.');
    }

    $stmt_insert->close();
    $stmt_update->close();
    $conn->close();
} else {
    // If not a POST request, redirect or handle appropriately
    die('Invalid request method.');
}
?>
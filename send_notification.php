<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and role is set
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role'])) {
    die('Access denied. Please log in.');
}

// Check if the logged-in user is a supervisor
if ($_SESSION['role'] !== 'supervisor') {
    die('Access denied. You must be a supervisor to view this page.');
}

// Require database connection
require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $notification_message = $_POST['notification_message'];

    // Insert notification into the notifications table
    $sql = "INSERT INTO notifications (project_id, notification_message, notified) VALUES (?, ?, 'notified')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $project_id, $notification_message);

    if ($stmt->execute()) {
        echo "Notification sent successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the notify page
    header("Location: supervisor_notification.php");
    exit;
}
?>
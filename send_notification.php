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
    $group_id = (int)$_POST['group_id'];
    $notification_message = trim($_POST['notification_message']); // Trim to remove any extra whitespace

    // Get current timestamp
    $created_at = date('Y-m-d H:i:s');

    // Check if the group_id exists in the groups table
    $group_check_sql = "SELECT project_id FROM groups WHERE id = ?";
    $stmt_group_check = $conn->prepare($group_check_sql);
    $stmt_group_check->bind_param("i", $group_id);
    $stmt_group_check->execute();
    $stmt_group_check->store_result();

    if ($stmt_group_check->num_rows === 0) {
        die('Invalid group ID.'); // Adding more detailed error message
    } else {
        echo "Valid group ID. <br>";
    }

    $stmt_group_check->bind_result($project_id);
    $stmt_group_check->fetch();
    $stmt_group_check->close();

    // Insert notification into the notifications table
    $sql_insert = "INSERT INTO notifications (group_id, notification_message, created_at, notified)
                   VALUES (?, ?, ?, 'notified')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iss", $group_id, $notification_message, $created_at);

    if ($stmt_insert->execute()) {
        // Notification successfully inserted
        $stmt_insert->close(); // Close statement after execution

        $_SESSION['notification_sent'] = true; // Store a flag in session for notification sent
        header('Location: supervisor_notification.php'); // Redirect to notification page
        exit();
    } else {
        // Error in SQL execution
        die('Failed to send notification. Please try again.');
    }

    // Close statement and database connection if not already closed
    $stmt_insert->close();
    $conn->close();
} else {
    // If not a POST request, redirect or handle appropriately
    die('Invalid request method.');
}
?>
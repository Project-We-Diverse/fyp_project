<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Check if project_id and status are provided in the GET request
if (!isset($_GET['project_id'], $_GET['status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Project ID or status not provided']);
    exit;
}

// Sanitize and validate project_id
$project_id = (int)$_GET['project_id'];
if ($project_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid project ID']);
    exit;
}

// Sanitize status
$status = $_GET['status'];
if (!in_array($status, ['In progress', 'Pending', 'Completed'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

// Update project status in the database
$update_sql = "UPDATE projects SET status = ? WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param('si', $status, $project_id);

if ($stmt->execute()) {
    // Close statement
    $stmt->close();
    $conn->close();

    // Redirect back to sub_fullDetails.php
    header('Location: sub_fullDetails.php?project_id=' . $project_id);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update status: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
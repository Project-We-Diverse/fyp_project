<?php
session_start();

require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// Retrieve user ID from session
$user_id = $_SESSION['id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch supervisor information from supervisors table
$stmt = $conn->prepare('SELECT * FROM supervisors WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$supervisor = $result->fetch_assoc();

// Check if supervisor data is found
if (!$supervisor) {
    echo "Supervisor data not found for user ID: " . $user_id;
    exit;
}

// Assign retrieved data to variables
$full_name = htmlspecialchars($supervisor['full_name']);
$supervisor_id = htmlspecialchars($supervisor['supervisor_id']); // Assuming this is the supervisor's identifier in your system

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Profile</title>
    <link rel="stylesheet" href="supervisor_profile.css">
    <link rel="icon" href="assets/favicon.png">
</head>
<body>
    <?php include "supervisor_bar.php"; ?>
    <div class="container">
        <h2>Supervisor's Profile</h2>

        <div class="profile-details">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></p>
            <p><strong>Full Name:</strong> <?php echo $full_name; ?></p>
            <p><strong>Supervisor Identifier:</strong> <?php echo $supervisor_id; ?></p>
        </div>
    </div>
</body>
</html>
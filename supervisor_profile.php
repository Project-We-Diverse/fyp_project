<?php
include "conn.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userID'];

$sql = "SELECT name, email_address FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Supervisor</title>
    <link rel="stylesheet" href="supervisor_profile.css" type="text/css">
</head>
<body>
    <?php include "supervisor_bar.php"; ?>
    <div class="main-content">
        <div class="title-container">
            <h2 class="title">Supervisor's Profile</h2>
        </div>

        <div class="profile-details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Email address:</strong> <?php echo htmlspecialchars($email); ?></p>
        </div>
    </div>
</body>
</html>

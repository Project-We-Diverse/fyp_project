<?php
// * only start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'conn.php';

// * check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// * retrieve user ID from session
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} else {
    echo "User ID is not set in the session.";
    exit;
}

$stmt = $conn->prepare('SELECT gender FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $gender = $user['gender'];
} else {
    echo "User not found.";
    exit;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Supervisor</title>
    <link rel="stylesheet" href="bar.css" type="text/css">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Supervisor Dashboard</h2>
        </div>

        <div class="profile">
            <a href="supervisor_profile.php">
                <?php if ($gender == "male"): ?>
                    <img src="assets/male.png" alt="Male Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php else: ?>
                    <img src="assets/female.png" alt="Female Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php endif; ?>
            </a>
        </div>
    </div>

    <div class="sidebar-container">
        <div class="sidebar-items">
            <ul>
                <li><a href="supervisor_dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i>Home</a></li>
                <li><a href="supervisor_student.php" class="sidebar-link"><i class="fa-solid fa-user-graduate"></i>Student</a></li>
                <li><a href="supervisor_submission.php" class="sidebar-link"><i class="fa-solid fa-file"></i>Submission</a></li>
                <li><a href="supervisor_notification.php" class="sidebar-link"><i class="fa-solid fa-bell"></i>Notification</a></li>
                <li class="logout"><a href="logout.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i>Log out</a></li>
            </ul>
        </div>
    </div>
</body>
</html>

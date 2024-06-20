<?php
// Only start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// Retrieve user ID from session
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} else {
    echo "User ID is not set in the session.";
    exit;
}


// SQL query to select data
$notification_sql = "SELECT notification_message, project_id FROM notifications";
$notification_result = $conn->query($notification_sql);

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="student_notification_style.css" text="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
    <title>Notifications</title>
    <style>
        input[type="submit"] {
            background-color: #f9f9f9;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        input[type="submit"]:hover {
            background-color: #e6e6e6;
        }
    </style>
</head>
<?php include "student_bar.php"; ?>
<body>

    <div class="notification-details-container">
        <h1>Notifications</h1>
        <?php
if ($notification_result->num_rows > 0) {
    // Loop through notifications and display them in boxes
    while($row = $notification_result->fetch_assoc()) {
      echo "<div class='notification-box'>";
      echo "<p>" . $row["notification_message"] . "</p>";
    echo "<form method='POST' action='clear_notificationstudent.php'>";
    echo "<input type='hidden' name='notification_id' value='" . $row["project_id"] . "'>";
    echo "<input type='submit' value='Clear'>";
    echo "</form>";
      echo "</div>";
    }
  } else {
    echo "No notifications found.";
  }
  ?>
    </div>
</body>
</html>
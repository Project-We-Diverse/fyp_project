<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'conn.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $username = $_SESSION['username'];
} else {
    echo "User ID is not set in the session.";
    exit;
}

if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} else {
    echo "Student ID is not set in the session.";
    exit;
}

if (isset($_SESSION['intake_id'])) {
    $intake_id = $_SESSION['intake_id'];
} else {
    echo "Intake ID is not set in the session.";
    exit;


}

// Fetch user gender
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
    <title>Home - Student</title>
    <link rel="stylesheet" href="student_dashboard.css" text="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
    <style>
        <?php include "student_dashboard.css"; ?>
    </style>
</head>
<body>
    <?php include "student_bar.php"; ?>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Student Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
        </div>

        <div class="profile">
            <a href="student_info.php">
                <?php if ($gender == "male"): ?>
                    <img src="assets/male.png" alt="Male Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php else: ?>
                    <img src="assets/female.png" alt="Female Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php endif; ?>
            </a>
        </div>
        
       
        
        <div class="project-container">
        <?php
        // Fetch and display projects assigned to the logged-in student's intake, ordered by status
        $sql = "SELECT id, project_name, description, status, is_group_project 
                FROM projects 
                WHERE intake_id = ?
                ORDER BY CASE 
                    WHEN status = 'In progress' THEN 1
                    WHEN status = 'Pending' THEN 2
                    WHEN status = 'Completed' THEN 3
                END";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $intake_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            echo "Error: " . $conn->error;
        } else {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = '';
                    switch ($row["status"]) {
                        case 'Completed':
                            $status_class = 'status-completed';
                            break;
                        case 'In progress':
                            $status_class = 'status-in-progress';
                            break;
                        case 'Pending':
                            $status_class = 'status-pending';
                            break;
                    }

                    echo '<div class="project-box">';
                    echo '<a class="project-link" href="student_project_details.php?project_id=' . $row['id'] . '">';
                    echo '<div class="project-details">';
                    echo '<p class="project-name">';
                    // Check if it's a group project or individual project and display the appropriate icon
                    if ($row["is_group_project"] == 1) {
                        // Group project
                        echo '<i class="fa-solid fa-user-group"></i>'; // Display group icon
                    } else {
                        // Individual project
                        echo '<i class="fa-solid fa-user"></i>'; // Display individual icon
                    }
                    echo '<span class="icon-title">' . htmlspecialchars($row["project_name"]) . '</span></p>'; // Project name with icon
                    echo '<p>' . htmlspecialchars($row["description"]) . '</p>';
                    echo '<p class="project-status ' . $status_class . '">' . htmlspecialchars($row["status"]) . '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                // Display circle exclamation icon when no projects are found
                echo '<p class="no-projects"><i class="fa-solid fa-circle-exclamation"></i> No projects found.</p>';
            }
            $result->free();
        }

        $stmt->close();
        $conn->close();
        ?>
        </div>
    </div>
</body>
</html>


<!-- <div class="notification">
                    <a href="student_notifications.php">
                        <div class="notBtn" href="#" class="sidebar-link"><i class="fa-solid fa-bell"></i>Notifications
                        <div class="number"><?php echo $notification_count;?></div>
                    </a>
                    <div class="box">
                        <div class="display">
                            <div class="nothing"> 
                                <i class="fas fa-child stick"></i> 
                                <div class="cent">Looks Like your all caught up!</div>
                            </div>
                            <div class="cont">
                            <?php
                            // SQL query to select data
                            $notification_sql = "SELECT notification_message, project_id FROM notifications";
                            $notification_result = $conn->query($notification_sql);

                            if ($notification_result->num_rows > 0) {
                                // Loop through notifications and display them
                                while($row = $notification_result->fetch_assoc()) {
                                    echo "<div class='notification-box'>";
                                    echo "<p>" . $row["notification_message"] . "</p>";
                                    echo "</div>";
                                }
                            } else {
                                echo "No notifications found.";
                            }
                            ?>
                            </div>
                        </div>
                    </div> -->
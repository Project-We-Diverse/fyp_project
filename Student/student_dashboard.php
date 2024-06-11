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



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Student</title>
    <link rel="stylesheet" href="bar.css" text="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
    <style>
        <?php include "student_dashbaord.css"; ?>
    </style>
</head>
<body>
    <?php include "student_bar.php"; ?>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Student Dashboard</h2>
        </div>

        <div class="profile">
            <a href="student_profile.php">
                <?php if ($gender == "male"): ?>
                    <img src="assets/male.png" alt="Male Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php else: ?>
                    <img src="assets/female.png" alt="Female Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php endif; ?>
            </a>
        </div>
        
         <div class="project-container">
        <?php
        // Fetch and display projects assigned to the logged-in sstudent, ordered by status
        $sql = "SELECT id, project_name, description, status, is_group_project 
                FROM projects 
                WHERE student_id = $user_id
                ORDER BY CASE 
                    WHEN status = 'In progress' THEN 1
                    WHEN status = 'Pending' THEN 2
                    WHEN status = 'Completed' THEN 3
                END";

        $result = $conn->query($sql);

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
    </div>


</body>
</html>
<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// Get the user_id of the logged-in supervisor from the session
$user_id = $_SESSION['id'];

// Fetch supervisor's intake_id using user_id
$sql_supervisor = "SELECT intake_id FROM supervisors WHERE user_id = $user_id";
$result_supervisor = $conn->query($sql_supervisor);

if ($result_supervisor && $result_supervisor->num_rows > 0) {
    $row_supervisor = $result_supervisor->fetch_assoc();
    $supervisor_intake_id = $row_supervisor['intake_id'];
} else {
    echo "No supervisor found with User ID: " . $user_id;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Supervisor</title>
    <link rel="stylesheet" href="supervisor_dashboard.css" type="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .icon-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include "supervisor_bar.php"; ?>

    <div class="project-container">
        <?php
        // Fetch and display projects assigned to the logged-in supervisor, ordered by status
        $sql = "SELECT p.id, p.project_name, p.description, p.status, p.is_group_project 
                FROM projects p
                WHERE p.intake_id = $supervisor_intake_id
                ORDER BY CASE 
                    WHEN p.status = 'In progress' THEN 1
                    WHEN p.status = 'Pending' THEN 2
                    WHEN p.status = 'Completed' THEN 3
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
                    echo '<a class="project-link" href="sub_fullDetails.php?project_id=' . $row['id'] . '">';
                    echo '<div class="project-details">';
                    echo '<p class="project-name">';
                    if ($row["is_group_project"] == 1) {
                        echo '<i class="fa-solid fa-user-group"></i>';
                    } else {
                        echo '<i class="fa-solid fa-user"></i>';
                    }
                    echo '<span class="icon-title">' . htmlspecialchars($row["project_name"]) . '</span></p>';
                    echo '<p>' . htmlspecialchars($row["description"]) . '</p>';
                    echo '<p class="project-status ' . $status_class . '">' . htmlspecialchars($row["status"]) . '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p class="no-projects"><i class="fa-solid fa-circle-exclamation"></i> No projects found.</p>';
            }
            $result->free();
        }

        $conn->close();
        ?>
    </div>
</body>
</html>

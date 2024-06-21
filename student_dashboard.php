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

// Fetch student details based on the logged-in user
$stmt = $conn->prepare('SELECT student_id, intake_id FROM students WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if ($student) {
    $student_id = $student['student_id'];
    $intake_id = $student['intake_id'];
    $_SESSION['student_id'] = $student_id;
    $_SESSION['intake_id'] = $intake_id;
} else {
    echo "Student details not found.";
    exit;
}
$stmt->close();

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
        .icon-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include "student_bar.php"; ?>
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
        $sql = " SELECT p.id, p.project_name, p.description, p.status, p.is_group_project 
            FROM projects p
            LEFT JOIN groups g ON p.id = g.project_id
            LEFT JOIN group_members gm ON g.id = gm.group_id
            WHERE p.intake_id = ? OR gm.student_id = ?
            GROUP BY p.id
            ORDER BY CASE 
                WHEN p.status = 'In progress' THEN 1
                WHEN p.status = 'Pending' THEN 2
                WHEN p.status = 'Completed' THEN 3
            END";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $intake_id, $student_id);
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

            $stmt->close();
            $conn->close();
            ?>

        </div>
    </div>
</body>
</html>
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Supervisor</title>
    <link rel="stylesheet" href="bar.css" type="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <style>
        .project-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 1.7%;
            margin-left: 13%;
            max-width: 1185px;
        }

        .project-box {
            background-color: #ffffff;
            border: 1px solid #c0c0c0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: 185px;
            transition: box-shadow 0.3s, transform 0.3s;
            box-sizing: border-box; 
        }

        .project-box:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .project-link {
            display: block;
            text-decoration: none;
            color: inherit;
            height: 100%;
        }

        .project-details p:first-child {
            font-weight: bold;
            font-size: 17px;
            margin-top: 0;
        }

        .project-status {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            display: inline-block;
            margin-top: 20%;
        }

        .status-completed {
            background-color: #008000;
        }

        .status-in-progress {
            background-color: #FFA500;
        }

        .status-pending {
            background-color: #FF0000;
        }
    </style>
</head>
<body>
    <?php include "supervisor_bar.php"; ?>

    <div class="project-container">
        <?php
        // Fetch and display projects ordered by status
        $sql = "SELECT id, project_name, description, status 
                FROM projects 
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
                    echo '<a class="project-link" href="supervisor_project_details.php?project_id=' . $row['id'] . '">';
                    echo '<div class="project-details">';
                    echo '<p>' . htmlspecialchars($row["project_name"]) . '</p>';
                    echo '<p>' . htmlspecialchars($row["description"]) . '</p>';
                    echo '<p class="project-status ' . $status_class . '">' . htmlspecialchars($row["status"]) . '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No projects found.</p>';
            }
            $result->free();
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
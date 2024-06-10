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

// Get the ID of the logged-in supervisor from the session
$supervisor_id = $_SESSION['id'];

// Check if project_id is set in the GET request and sanitize it
if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
    $project_id = (int)$_GET['project_id']; // Cast to integer to sanitize
} else {
    echo "Error: Project ID is not provided.";
    exit; // Terminate the script if project_id is not provided
}

// Fetch project details
$project_sql = "SELECT p.id AS project_id,
                        p.project_name AS project_title,
                        u.username AS supervisor_name,
                        m.name AS module,
                        p.status AS status
                FROM projects p
                INNER JOIN users u ON p.supervisor_id = u.id
                INNER JOIN modules m ON p.module_id = m.id
                WHERE p.supervisor_id = $supervisor_id
                AND p.id = $project_id";

$project_result = $conn->query($project_sql);

if (!$project_result) {
    die('Query Error: ' . $conn->error);
}

$project_row = $project_result->fetch_assoc();

// Fetch all submissions for the project
$submissions_sql = "SELECT s.submission_title AS submission_title,
                           s.submission_date AS submission_date,
                           s.document_name AS document_name,
                           s.document_path AS document_path,
                           s.id AS submission_id
                    FROM submissions s
                    WHERE s.project_id = $project_id
                    ORDER BY s.submission_date DESC";

$submissions_result = $conn->query($submissions_sql);

if (!$submissions_result) {
    die('Query Error: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details - Supervisor</title>
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .project-details-container {
            margin: 20px;
            margin-left: 12%;
        }

        .project-details-box {
            padding-top: 10px;
            margin-bottom: 20px;
        }

        .project-details-box h2 {
            margin-top: 0;
            font-size: 30px;
        }

        .project-details-box h3 {
            margin-top: 0;
            font-size: 18px;
        }

        .project-details-box p {
            margin: 10px 0;
            padding-top: 8px;
            font-size: 14px;
        }

        .document-link {
            color: #000000;
            text-decoration: underline;
            cursor: pointer;
        }

        .document-link:hover {
            text-decoration: underline;
            color: #4169e1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include "supervisor_bar.php"; ?>
    <div class="project-details-container">
        <?php
        if ($project_row) {
            ?>
            <div class="project-details-box">
                <h2><strong><?php echo htmlspecialchars($project_row["project_title"]); ?></strong></h2>
                <hr class="divider">
                <p><strong>Supervisor:</strong> <?php echo htmlspecialchars($project_row["supervisor_name"]); ?></p>
                <p><strong>Module:</strong> <?php echo htmlspecialchars($project_row["module"]); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($project_row["status"]); ?></p>
            </div>

            <h3>Submissions:</h3>
            <?php
            if ($submissions_result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>Submission Title</th><th>Submission Date</th><th>Document Name</th>';
                while ($submission_row = $submissions_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><a href="specific_subDetails.php?submission_id=' . htmlspecialchars($submission_row["submission_id"]) . '" class="document-link">' . htmlspecialchars($submission_row["submission_title"]) . '</a></td>';
                    echo '<td>' . htmlspecialchars($submission_row["submission_date"]) . '</td>';
                    echo '<td>' . htmlspecialchars($submission_row["document_name"]) . '</td>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No submissions found.</p>';
            }
        } else {
            echo '<p>No project details found.</p>';
        }
        ?>
    </div>
</body>
</html>
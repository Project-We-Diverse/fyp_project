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

// Get the ID of the logged-in user from the session
$user_id = $_SESSION['id'];

// Get the student ID of the logged-in user from the session
$student_id = $_SESSION['student_id'];

// Check if project_id is set in the GET request and sanitize it
if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
    $project_id = (int)$_GET['project_id']; // Cast to integer to sanitize
} else {
    echo "Error: Project ID is not provided.";
    exit; // Terminate the script if project_id is not provided
}

// Fetch project details based on project_id and student_id
$project_sql = "SELECT p.id AS project_id,
                        p.project_name AS project_title,
                        u.username AS full_name,
                        m.name AS module,
                        p.status AS status
                FROM projects p
                INNER JOIN students s ON p.intake_id = s.intake_id
                INNER JOIN users u ON s.user_id = u.id
                INNER JOIN modules m ON p.module_id = m.id
                WHERE p.id = ? AND s.user_id = ?";

$stmt = $conn->prepare($project_sql);
$stmt->bind_param('ii', $project_id, $user_id);
$stmt->execute();
$project_result = $stmt->get_result();

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
                    WHERE s.project_id = ?
                    ORDER BY s.submission_date DESC";

$stmt = $conn->prepare($submissions_sql);
$stmt->bind_param('i', $project_id);
$stmt->execute();
$submissions_result = $stmt->get_result();

if (!$submissions_result) {
    die('Query Error: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details - user</title>
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
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 20px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include "student_bar.php"; ?>
    <div class="project-details-container">
        <?php
        if ($project_row) {
            ?>
            <div class="project-details-box">
                <h2><strong><?php echo htmlspecialchars($project_row["project_title"], ENT_QUOTES, 'UTF-8'); ?></strong></h2>
                <hr class="divider">
                <p><strong>Student:</strong> <?php echo htmlspecialchars($project_row["full_name"], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Module:</strong> <?php echo htmlspecialchars($project_row["module"], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($project_row["status"], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <h3>Submissions:</h3>
            <?php
            if ($submissions_result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>Submission Title</th><th>Submission Date</th><th>Document Name</th>';
                while ($submission_row = $submissions_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($submission_row["document_name"], ENT_QUOTES, 'UTF-8') . '</td>';
                    echo '<td>' . htmlspecialchars($submission_row["submission_date"], ENT_QUOTES, 'UTF-8') . '</td>';
                    echo '<td><a href="student_sub_details.php?submission_id=' . htmlspecialchars($submission_row["submission_id"], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($submission_row["submission_title"], ENT_QUOTES, 'UTF-8') . '</a></td>';
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

        <h3>Submit New Assignment</h3>
        <?php include "student_submit.php"; ?>
    </div>
</body>
</html>
<?php
session_start();

require 'conn.php';
include 'supervisor_bar.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// Get the supervisor's user ID from the session
$supervisor_id = $_SESSION['id']; // Adjust this based on how you store the user ID

// Debugging: Check if supervisor ID is set
if (empty($supervisor_id)) {
    die('Supervisor ID is not set in session.');
}

// Fetch approved submissions related to the supervisor
$sql = "SELECT s.id AS submission_id, s.submission_title AS submission_title, s.submission_date AS submission_date, p.project_name AS project_title, s.checked
        FROM submissions s
        INNER JOIN projects p ON s.project_id = p.id
        WHERE s.status = 'approved' AND p.supervisor_id = ?
        ORDER BY s.checked ASC, s.submission_date ASC";

$stmt = $conn->prepare($sql);

// Debugging: Check if the statement was prepared correctly
if (!$stmt) {
    die('Prepare Error: ' . $conn->error);
}

$stmt->bind_param('i', $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();

// Debugging: Check if the query executed correctly
if (!$result) {
    die('Query Error: ' . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <title>New Submissions - Supervisor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .content-container {
            flex-grow: 1;
            padding: 20px;
            max-width: 1200px;
            margin-left: 11%;
        }

        h2 {
            color: #333;
            font-size: 23px;
            margin-top: 1%;
        }

        .submission-list {
            list-style-type: none;
            padding: 0;
        }

        .submission-item {
            background-color: #fff;
            margin: 10px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            position: relative;
            cursor: pointer; /* Added cursor pointer for better UX */
        }

        .submission-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .submission-item .title {
            font-weight: bold;
            font-size: 1.4em;
            margin-bottom: 10px;
            color: #0056b3; /* Changed title color for better visibility */
        }

        .submission-item .project-title {
            margin-bottom: 5px; /* Added margin-bottom to create space between project title and submission date */
            color: #000; /* Changed project title color to black */
        }

        .submission-item .submission-date-label {
            color: #000; /* Changed submission date label color to black */
        }

        .submission-item .submission-date {
            color: #28a745; /* Changed submission date color to green */
            font-weight: bold;
        }

        .status-checked {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #28a745;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .submission-item:hover .submission-details {
            background-color: #f5f5f5;
        }

        .submission-link {
            text-decoration: none; 
            color: inherit;
            display: block;
            padding: 10px; 
            border-radius: inherit;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h2>New Submissions</h2>
        <ul class="submission-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="submission-item">
                    <a href="sup_newSub_details.php?id=<?php echo $row['submission_id']; ?>" class="submission-link">
                        <div class="title"><?php echo htmlspecialchars($row['submission_title']); ?></div>
                        <div class="project-title">Project name: <?php echo htmlspecialchars($row['project_title']); ?></div>
                        <div class="submission-date-label">Submission date: <span class="submission-date"><?php echo htmlspecialchars($row['submission_date']); ?></span></div>
                        <?php if ($row['checked'] == 'checked'): ?>
                            <div class="status-checked">Checked</div>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
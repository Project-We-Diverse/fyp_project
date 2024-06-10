<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'conn.php';
include 'supervisor_bar.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// Get the ID of the logged-in supervisor from the session
$supervisor_id = $_SESSION['id'];

// Verify the supervisor ID
if (!isset($supervisor_id)) {
    die('Supervisor ID is not set in the session.');
}

// Fetch new submissions for the supervisor that are approved
$sql = "SELECT s.id AS submission_id, 
                s.submission_title, 
                s.submission_date, 
                p.project_name 
        FROM submissions s 
        INNER JOIN projects p ON s.project_id = p.id 
        WHERE p.supervisor_id = ? AND s.status = 'approved'";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param('i', $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Submissions - Supervisor</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 13   px;
        }

        table {
            border-collapse: collapse;
            width: 86%;
            margin-left: 12%;
        }

        th, td {
            border: 1px solid #c0c0c0;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        h2 {
            margin-left: 12%;
            margin-top: 2.2%;
            font-size: 23px;
        }

        .action-cell {
            width: 1%; /* Set the width of the table cell to 1% */
            white-space: nowrap; /* Prevent wrapping */
        }

        .check-button {
            display: block; /* Set display to block */
            width: 70%; 
            background-color: #4CAF50;
            color: white;
            padding: 10px; /* Adjust padding as needed */
            border: none;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            text-align: center; /* Center the button text */
        }

        .check-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>Approved Submissions</h2>

<table>
    <tr>
        <th>Submission Title</th>
        <th>Project Name</th>
        <th>Submission Date</th>
        <th>Action</th>
    </tr>
    <?php
    if ($result->num_rows === 0) {
        echo '<tr><td colspan="4">No approved submissions found for this supervisor.</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row["submission_title"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["project_name"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["submission_date"]) . '</td>';
            echo '<td class="action-cell"><a href="sup_newSub_details.php?submission_id=' . $row["submission_id"] . '" class="check-button">Check</a></td>';
            echo '</tr>';
        }
    }
    ?>
</table>

</body>
</html>
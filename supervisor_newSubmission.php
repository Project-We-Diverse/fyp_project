<?php
// Start the session if it is not already started
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
$sql = "SELECT s.id AS submission_id, s.submission_title AS submission_title, s.submission_date AS submission_date, p.project_name AS project_title
        FROM submissions s
        INNER JOIN projects p ON s.project_id = p.id
        WHERE s.status = 'approved' AND p.supervisor_id = ?
        ORDER BY s.submission_date ASC";

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

        h2 {
            font-size: 23px;
        }
        .submission-list-container {
            margin: 20px;
            margin-left: 12%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .submitBtn {
            padding: 8px 10px;
            background-color: #007BFF;
            color: white;
            border: 1px solid #4169e1;
            border-radius: 5px;
            cursor: pointer;
        }
        .less-prominent {
            background-color: #f0f0f0;
            color: #777;
            border-color: #c0c0c0;
            cursor: not-allowed;
        }
        .disabled {
            cursor: not-allowed;
            background-color: #ccc;
            color: #000000;
            border-color: #999;
        }
        .submission-title {
            color: #0056b3;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="submission-list-container">
        <h2>New Submissions</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Submission Title</th>
                        <th>Project Title</th>
                        <th>Submission Date</th>
                        <th>Checked?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php $submission_id = $row['submission_id']; ?>
                        <tr>
                            <td>
                                <a href="sup_newSub_details.php?id=<?php echo $submission_id; ?>" class="submission-title">
                                    <?php echo htmlspecialchars($row['submission_title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($row['project_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                            <td>
                                <?php if (in_array($submission_id, $_SESSION['checked_submissions'] ?? [])): ?>
                                    <button disabled class="submitBtn disabled">Checked</button>
                                <?php else: ?>
                                    <form id="checkForm_<?php echo $submission_id; ?>" action="check_submission.php" method="post">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
                                        <button type="button" class="submitBtn less-prominent">Check</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No approved submissions found.</p>
        <?php endif; ?>
    </div>
    <script>
        function submitForm(submissionId) {
            var form = document.getElementById('checkForm_' + submissionId);
            form.submit();
        }
    </script>
</body>
</html>
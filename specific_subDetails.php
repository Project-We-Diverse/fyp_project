<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Details</title>
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .submission-details-container {
            margin: 20px;
            margin-left: 12%;
            position: relative;
        }

        .submission-details-box {
            padding-top: 10px;
            margin-bottom: 20px;
        }

        .submission-details-box h2 {
            margin-top: 0;
            font-size: 30px;
        }

        .submission-details-box h3 {
            margin-top: 0;
            font-size: 18px;
        }

        .submission-details-box p {
            margin: 10px 0;
            padding-top: 8px;
            font-size: 14px;
        }

        .document-link {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }

        .feedback-form {
            margin-top: 20px;
        }

        .feedback-form textarea {
            width: 100%;
            height: 50px;
            margin-bottom: 10px;
        }

        .feedback-form button {
            padding: 8px 10px;
            background-color: #007BFF;
            color: white;
            border: 1px solid #4169e1;
            border-radius: 5px;
            cursor: pointer;
        }

        .feedback-form button:hover {
            background-color: #4169e1;
        }

        .feedback-form textarea {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        #marks-wrapper {
            display: flex;
            align-items: center;
            width: 150px;
        }

        #marks {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 8px;
            width: 100%;
        }

        #marks-label {
            margin-left: 5px;
            color: #999;
            font-weight: lighter;
        }

        .btn {
            display: inline-block;
            padding: 15px 15px;
            background-color: #FF4136;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, transform 0.3s;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .btn:hover {
            background-color: #E0322A;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
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

        // Check if submission_id is set in the GET request and sanitize it
        if (isset($_GET['submission_id']) && !empty($_GET['submission_id'])) {
            $submission_id = (int)$_GET['submission_id']; // Cast to integer to sanitize
        } else {
            echo "Error: Submission ID is not provided.";
            exit; // Terminate the script if submission_id is not provided
        }

        // Fetch submission details along with marks and feedback
        $sql = "SELECT s.submission_title AS submission_title,
                    s.submission_date AS submission_date,
                    s.document_name AS document_name,
                    s.document_path AS document_path,
                    p.project_name AS project_title,
                    sup.full_name AS supervisor_name,
                    m.name AS module,
                    p.status AS status,
                    s.marks AS marks,
                    s.feedback_to_student AS feedback_to_student,
                    s.feedback_to_admin AS feedback_to_admin
                FROM submissions s
                INNER JOIN projects p ON s.project_id = p.id
                INNER JOIN supervisors sup ON p.intake_id = sup.intake_id
                INNER JOIN modules m ON p.module_id = m.id
                WHERE s.id = $submission_id";

        $result = $conn->query($sql);

        if (!$result) {
            die('Query Error: ' . $conn->error);
        }

        $row = $result->fetch_assoc();
    ?>

    <?php include "supervisor_bar.php"; ?>
    <div class="submission-details-container">
        <?php
        if ($row) {
            ?>
            <div class="submission-details-box">
                <h2><strong><?php echo htmlspecialchars($row["submission_title"] ?? 'N/A'); ?></strong></h2>
                <h3><?php echo htmlspecialchars($row["project_title"] ?? 'N/A'); ?></h3> 
                <hr class="divider">
                <p><strong>Supervisor:</strong> <?php echo htmlspecialchars($row["supervisor_name"] ?? 'N/A'); ?></p>
                <p><strong>Module:</strong> <?php echo htmlspecialchars($row["module"] ?? 'N/A'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($row["status"] ?? 'N/A'); ?></p>
                <p><strong>Submission Date:</strong> <?php echo htmlspecialchars($row["submission_date"] ?? 'N/A'); ?></p>
                <p><strong>Document Name:</strong> <?php echo htmlspecialchars($row["document_name"] ?? 'N/A'); ?></p>
                <p><strong>Submitted Document:</strong> <a href="<?php echo htmlspecialchars($row["document_path"] ?? '#'); ?>" target="_blank" class="document-link">View</a></p>
                <p><strong>Marks:</strong> <?php echo htmlspecialchars($row["marks"] ?? 'N/A'); ?> / 100</p>
            </div>

            <div class="feedback-form">
                <h3>Feedback to Student:</h3>
                <p><?php echo htmlspecialchars($row["feedback_to_student"] ?? 'No feedback provided.'); ?></p>
                <h3>Feedback to Admin:</h3>
                <p><?php echo htmlspecialchars($row["feedback_to_admin"] ?? 'No feedback provided.'); ?></p>
            </div>

            <a href="generate_report.php?submission_id=<?php echo $submission_id; ?>" class="btn"><i class="fa-solid fa-square-plus"></i> Generate Report</a>
            <?php
        } else {
            echo '<p>No submission details found.</p>';
        }
        ?>
    </div>
</body>
</html>

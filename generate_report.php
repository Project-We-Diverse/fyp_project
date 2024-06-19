<?php
// Start the session if it is not already started
session_start();

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

// Function to fetch submission details
function getSubmissionDetails($conn, $submission_id) {
    $sql = "SELECT 
                s.submission_title AS submission_title,
                s.submission_date AS submission_date,
                s.document_name AS document_name,
                s.document_path AS document_path,
                p.project_name AS project_title,
                sup.full_name AS supervisor_name,
                m.name AS module_name,
                p.start_date AS project_start_date,
                p.end_date AS project_end_date,
                s.marks AS marks,
                s.feedback_to_student AS feedback_to_student,
                s.feedback_to_admin AS feedback_to_admin, 
                i.name AS intake_name
            FROM 
                submissions s
                INNER JOIN projects p ON s.project_id = p.id
                INNER JOIN supervisors sup ON p.intake_id = sup.intake_id
                INNER JOIN modules m ON p.module_id = m.id
                INNER JOIN intakes i ON p.intake_id = i.id
            WHERE 
                s.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Fetch submission details
$submissionDetails = getSubmissionDetails($conn, $submission_id);

if (!$submissionDetails) {
    echo "Error: No submission details found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Report</title>
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .report-container {
            max-width: 800px;
            margin-left: 25%;
            margin-top: 2.4%;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: #fffafa;
        }

        .report-box {
            margin-bottom: 20px;
        }

        .report-box h2 {
            font-size: 28px;
            color: #000000;
            margin-bottom: 10px;
        }

        .report-box h3 {
            font-size: 18px;
            color: #000000;
            margin-bottom: 5px;
        }

        .report-box p {
            font-size: 14px;
            color: #000000;
            margin: 10px 0;
            font-weight: lighter;
        }

        .document-link {
            color: #0000ff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .document-link:hover {
            color: #00008b;
            text-decoration: underline;
        }

        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #696969;
        }

        h2, h3, h4 {
            margin-bottom: 10px;
        }

        strong {
            color: #555;
        }

        .feedback-section {
            padding: 1px 10px;
            border: 1px solid #696969;
            border-radius: 5px;
        }

        .feedback-section h4 {
            color: #555;
            margin-top: 2px;
            margin-bottom: -5px;
            padding-top: 9px;
        }

        .feedback-section p {
            font-size: 12px;
            color: #000000;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include "supervisor_bar.php"; ?>
    <div class="report-container">
        <div class="report-box">
            <h2><?php echo htmlspecialchars($submissionDetails["submission_title"] ?? 'N/A'); ?></h2>
            <h3><?php echo htmlspecialchars($submissionDetails["project_title"] ?? 'N/A'); ?></h3> 
            <hr>
            <p><strong>Supervisor:</strong> <?php echo htmlspecialchars($submissionDetails["supervisor_name"] ?? 'N/A'); ?></p>
            <p><strong>Module:</strong> <?php echo htmlspecialchars($submissionDetails["module_name"] ?? 'N/A'); ?></p>
            <p><strong>Project Start Date:</strong> <?php echo htmlspecialchars($submissionDetails["project_start_date"] ?? 'N/A'); ?></p>
            <p><strong>Project End Date:</strong> <?php echo htmlspecialchars($submissionDetails["project_end_date"] ?? 'N/A'); ?></p>
            <p><strong>Submission Date:</strong> <?php echo htmlspecialchars($submissionDetails["submission_date"] ?? 'N/A'); ?></p>
            <p><strong>Document Name:</strong> <?php echo htmlspecialchars($submissionDetails["document_name"] ?? 'N/A'); ?></p>
            <p><strong>Submitted Document:</strong> <a href="<?php echo htmlspecialchars($submissionDetails["document_path"] ?? '#'); ?>" target="_blank" class="document-link">View</a></p>
            <p><strong>Marks:</strong> <?php echo htmlspecialchars($submissionDetails["marks"] ?? 'N/A'); ?> / 100</p>
            <p><strong>Intake:</strong> <?php echo htmlspecialchars($submissionDetails["intake_name"] ?? 'N/A'); ?></p>
            <div class="feedback-section">
                <h4>Feedback to Student:</h4>
                <p><?php echo htmlspecialchars($submissionDetails["feedback_to_student"] ?? 'No feedback provided.'); ?></p>
                <h4>Feedback to Admin:</h4>
                <p><?php echo htmlspecialchars($submissionDetails["feedback_to_admin"] ?? 'No feedback provided.'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>

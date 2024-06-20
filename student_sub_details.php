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
               p.project_name AS project_name,
               u.username AS student_name,
               m.name AS module,
               p.status AS status,
               s.marks AS marks,
               s.feedback_to_student AS feedback_to_student,
               s.feedback_to_admin AS feedback_to_admin
        FROM submissions s
        INNER JOIN projects p ON s.project_id = p.id
        INNER JOIN students st ON st.user_id = ?
        INNER JOIN users u ON st.user_id = u.id
        INNER JOIN modules m ON p.module_id = m.id
        WHERE s.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $_SESSION['id'], $submission_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die('Query Error: ' . $conn->error);
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Details</title>
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .submission-details-container {
            margin: 20px;
            margin-left: 12%;
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

        /* Style for input box */
        #marks-wrapper {
            display: flex;
            align-items: center;
            width: 150px; /* Adjust width as needed */
        }

        #marks {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 8px;
            width: 100%; /* Adjust width as needed */
        }

        /* Style for '/ 100' */
        #marks-label {
            margin-left: 5px;
            color: #999; /* Lighter color */
            font-weight: lighter; /* Lighter font weight */
        }
    </style>
</head>
<body>
    <?php include "student_bar.php"; ?>
    <div class="submission-details-container">
        <?php
        if ($row) {
            ?>
            <div class="submission-details-box">
                <h2><strong><?php echo htmlspecialchars($row["submission_title"] ?? 'N/A'); ?></strong></h2>
                <h3><?php echo htmlspecialchars($row["project_name"] ?? 'N/A'); ?></h3> 
                <hr class="divider">
                <p><strong>Student:</strong> <?php echo htmlspecialchars($row["student_name"] ?? 'N/A'); ?></p>
                <p><strong>Module:</strong> <?php echo htmlspecialchars($row["module"] ?? 'N/A'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($row["status"] ?? 'N/A'); ?></p>
                <p><strong>Submission Date:</strong> <?php echo htmlspecialchars($row["submission_date"] ?? 'N/A'); ?></p>
                <p><strong>Document Name:</strong> <?php echo htmlspecialchars($row["document_name"] ?? 'N/A'); ?></p>
                <p><strong>Submitted Document:</strong> <a href="<?php echo htmlspecialchars($row["document_path"] ?? '#'); ?>" target="_blank" class="document-link">View</a></p>
                
                <!-- Display marks -->
                <p><strong>Marks:</strong> <?php echo htmlspecialchars($row["marks"] ?? 'N/A'); ?> / 100</p>
            </div>

            <div class="feedback-form">
                <h3>Feedback to Supervisor:</h3>
                <p><?php echo htmlspecialchars($row["feedback_to_student"] ?? 'No feedback provided.'); ?></p>
            </div>
            <?php
        } else {
            echo '<p>No submission details found.</p>';
        }
        ?>
    </div>
</body>
</html>
<?php
// Start the session if it is not already started
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'conn.php';
include 'supervisor_bar.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// Check if submission_id is set in the GET request and sanitize it
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $submission_id = (int)$_GET['id']; // Cast to integer to sanitize
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
               st.full_name AS student_name,
               st.student_id AS student_card_id,
               sup.full_name AS supervisor_name,
               m.name AS module,
               s.status AS status,
               s.marks AS marks,
               s.feedback_to_student AS feedback_to_student,
               s.feedback_to_admin AS feedback_to_admin
        FROM submissions s
        INNER JOIN projects p ON s.project_id = p.id
        INNER JOIN supervisors sup ON p.intake_id = sup.intake_id
        INNER JOIN students st ON s.student_id = st.id
        INNER JOIN modules m ON p.module_id = m.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $submission_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die('Query Error: ' . $stmt->error);
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Details - Supervisor</title>
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
            height: 45px;
            margin-bottom: 10px;
        }
        .submitBtn {
            padding: 8px 10px;
            background-color: #007BFF;
            color: white;
            border: 1px solid #4169e1;
            border-radius: 5px;
            cursor: pointer;
        }
        .submitBtn:hover {
            background-color: #4169e1;
        }
        .feedback-form textarea {
            font-family: Arial, sans-serif;
            font-size: 11px;
            border-radius: 3px;
        }
        .marks-wrapper h3 {
            font-size: 13px;
            margin-right: 5px;
            margin-top: 10px;
        }
        .marks-wrapper span {
            margin-left: 1.2px;
            padding-right: 5px;
        }
        .marks-wrapper {
            display: flex;
            align-items: center;
            width: 20%;
        }
        .marks {
            border: 1px solid #c0c0c0;
            border-radius: 3px;
            padding: 8px;
            width: 21%;
            font-size: 12px; 
        }
        .marks-label {
            font-size: 15px;
            color: #999; /* Lighter color */
            font-weight: lighter; /* Lighter font weight */
        }
        .error-message {
            color: red;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="submission-details-container">
        <?php
        if ($row) {
            ?>
            <div class="submission-details-box">
                <h2><strong><?php echo htmlspecialchars($row["submission_title"] ?? 'N/A'); ?></strong></h2>
                <h3><?php echo htmlspecialchars($row["project_title"] ?? 'N/A'); ?></h3> 
                <hr class="divider">
                <p><strong>Student:</strong> <?php echo htmlspecialchars($row["student_name"] ?? 'N/A'); ?> (<?php echo htmlspecialchars($row["student_card_id"] ?? 'N/A'); ?>)</p>
                <p><strong>Supervisor:</strong> <?php echo htmlspecialchars($row["supervisor_name"] ?? 'N/A'); ?></p>
                <p><strong>Module:</strong> <?php echo htmlspecialchars($row["module"] ?? 'N/A'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($row["status"] ?? 'N/A'); ?></p>
                <p><strong>Submission Date:</strong> <?php echo htmlspecialchars($row["submission_date"] ?? 'N/A'); ?></p>
                <p><strong>Document Name:</strong> <?php echo htmlspecialchars($row["document_name"] ?? 'N/A'); ?></p>
                <p><strong>Submitted Document:</strong> <a href="<?php echo htmlspecialchars($row["document_path"] ?? '#'); ?>" target="_blank" class="document-link">View</a></p>
            </div>

            <div class="form">
                <form id="feedbackForm" action="submit_feedback.php" method="post">
                    <input type="hidden" name="submission_id" value="<?php echo htmlspecialchars($submission_id); ?>">

                    <div class="marks-wrapper">
                        <h3>Marks <span>(Required):</span></h3>
                        <input type="text" name="marks" class="marks" value="<?php echo htmlspecialchars($row["marks"] ?? ''); ?>" placeholder="Marks" required>
                        <span class="marks-label">/ 100</span>
                    </div>
                    <div class="error-message" id="marksError"></div>

                    <div class="feedback-form">
                        <h3>Feedback to Student and Admin:</h3>
                        <textarea name="feedback_to_student" placeholder="Write your feedback to the student..."><?php echo htmlspecialchars($row["feedback_to_student"] ?? ''); ?></textarea>
                        <textarea name="feedback_to_admin" placeholder="Write your feedback to the admin..."><?php echo htmlspecialchars($row["feedback_to_admin"] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" name="submit_feedback" class="submitBtn">Submit</button>
                </form>
            </div>
            <?php
        } else {
            echo '<p>No submission details found.</p>';
        }
        ?>
    </div>
    <script>
        document.getElementById('feedbackForm').addEventListener('submit', function(event) {
            const marksInput = document.querySelector('input[name="marks"]');
            const marksError = document.getElementById('marksError');
            const marksValue = parseFloat(marksInput.value);
            
            if (isNaN(marksValue) || marksValue < 0 || marksValue > 100) {
                event.preventDefault();
                marksError.textContent = 'Marks must be a number between 0 and 100.';
            } else {
                marksError.textContent = '';
            }
        });
    </script>
</body>
</html>

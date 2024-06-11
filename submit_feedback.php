<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'conn.php';

// Check if submission_id, marks, feedback_to_student, and feedback_to_admin are set in the POST request
if (isset($_POST['submission_id'], $_POST['marks'], $_POST['feedback_to_student'], $_POST['feedback_to_admin'])) {
    $submission_id = (int)$_POST['submission_id']; // Sanitize the input
    $marks = $_POST['marks'];
    $feedback_to_student = $conn->real_escape_string($_POST['feedback_to_student']);
    $feedback_to_admin = $conn->real_escape_string($_POST['feedback_to_admin']);

    // Update the database with the marks and feedback
    $sql = "UPDATE submissions 
            SET marks = '$marks', feedback_to_student = '$feedback_to_student', feedback_to_admin = '$feedback_to_admin'
            WHERE id = $submission_id";

    if ($conn->query($sql) === TRUE) {
        // Check if the session variable for checked submissions is set
        if (!isset($_SESSION['checked_submissions'])) {
            $_SESSION['checked_submissions'] = [];
        }

        // Add the submission_id to the session variable
        if (!in_array($submission_id, $_SESSION['checked_submissions'])) {
            $_SESSION['checked_submissions'][] = $submission_id;
        }

        // Redirect to supervisor_newSubmission.php
        header("Location: supervisor_newSubmission.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    echo "Error: All required fields are not provided.";
}
?>
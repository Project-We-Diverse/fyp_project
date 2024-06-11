<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if submission_id is set in the POST request
if (isset($_POST['submission_id'])) {
    $submission_id = (int)$_POST['submission_id']; // Sanitize the input

    // Check if the session variable for checked submissions is set
    if (!isset($_SESSION['checked_submissions'])) {
        $_SESSION['checked_submissions'] = [];
    }

    // Add the submission_id to the session variable
    if (!in_array($submission_id, $_SESSION['checked_submissions'])) {
        $_SESSION['checked_submissions'][] = $submission_id;
    }

    // Redirect back to the new submissions page
    header("Location: supervisor_newSubmission.php");
    exit();
} else {
    echo "Error: Submission ID is not provided.";
    exit();
}
?>
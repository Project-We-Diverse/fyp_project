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
    $feedback_to_student = trim($_POST['feedback_to_student']);
    $feedback_to_admin = trim($_POST['feedback_to_admin']);

    // Set default values if feedback fields are empty
    if (empty($feedback_to_student)) {
        $feedback_to_student = "No feedback by the supervisor";
    }
    if (empty($feedback_to_admin)) {
        $feedback_to_admin = "No feedback by the supervisor";
    }

    // Update the database with the marks, feedback, and set checked = 'checked'
    $sql = "UPDATE submissions 
            SET marks = ?, feedback_to_student = ?, feedback_to_admin = ?, checked = 'checked'
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
    
    $stmt->bind_param("sssi", $marks, $feedback_to_student, $feedback_to_admin, $submission_id);
    
    if ($stmt->execute()) {
        // Redirect to supervisor_newSubmission.php
        header("Location: supervisor_newSubmission.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: All required fields are not provided.";
}
?>
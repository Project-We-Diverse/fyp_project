<?php
// Start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $submission_id = $_POST['submission_id'];
    $feedback_to_student = $_POST['feedback_to_student'];
    $feedback_to_admin = $_POST['feedback_to_admin'];

    // Update feedback data in the database for the specific submission ID
    $sql = "UPDATE submissions SET feedback_to_student = ?, feedback_to_admin = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $feedback_to_student, $feedback_to_admin, $submission_id);

    if ($stmt->execute()) {
        // Redirect back to the details page with submission ID in the URL
        header("Location: sub_full_details.php?submission_id=$submission_id");
        exit();
    } else {
        echo "Error submitting feedback: " . $conn->error;
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
}
?>
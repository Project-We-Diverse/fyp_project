<?php
session_start();

// Include database connection file
require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $intake = $_POST['intake'];
    $semester = $_POST['semester'];
    $module = $_POST['module'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $assignment_type = $_POST['assignment_type'];

    // Debug statements
    echo "Intake: $intake, Semester: $semester, Module: $module, Title: $title, Description: $description, Start Date: $start_date, End Date: $end_date, Assignment Type: $assignment_type";

    // Determine whether it's a group assignment or individual assignment
    if ($assignment_type === 'group') {
        // Redirect to create_group_assignment.php
        header('Location: create_group_assignment.php?intake=' . $intake . '&semester=' . $semester . '&module=' . $module . '&title=' . urlencode($title) . '&description=' . urlencode($description) . '&due_date=' . $end_date);
        exit;
    } else {
        // Insert individual assignment into projects table
        $sql = "INSERT INTO projects (project_name, description, start_date, end_date, status, is_group_project, intake_id, semester_id, module_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Set default values for some parameters
            $status = 'In progress';
            $is_group_project = 0; // Assuming it's not a group project
            // Bind parameters
            $stmt->bind_param("sssssiisi", $title, $description, $start_date, $end_date, $status, $is_group_project, $intake, $semester, $module);
            // Execute statement
            if (!$stmt->execute()) {
                // Log the error instead of echoing
                error_log("Error executing SQL: " . $stmt->error);
                // Redirect to an error page
                header('Location: error.php');
                exit;
            }
        } else {
            // Log the error instead of echoing
            error_log("Error preparing SQL: " . $conn->error);
            // Redirect to an error page
            header('Location: error.php');
            exit;
        }
        // Close statement
        $stmt->close();
    }
    // Close connection
    $conn->close();

    
    header('Location: admin_submission.php');
    exit;
}
?>

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

    // Insert group assignment into projects table
    $sql = "INSERT INTO projects (project_name, description, start_date, end_date, status, is_group_project, intake_id, semester_id, module_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Set default values for some parameters
        $status = 'In progress';
        $is_group_project = 1; // Group project
        // Bind parameters
        $stmt->bind_param("sssssiisi", $title, $description, $start_date, $end_date, $status, $is_group_project, $intake, $semester, $module);
        // Execute statement
        if ($stmt->execute()) {
            // Store the insert ID before closing the statement
            $insert_id = $stmt->insert_id;
        } else {
            // Log the error instead of echoing
            error_log("Error executing SQL: " . $stmt->error);
            // Redirect to an error page
            header('Location: error.php');
            exit;
        }
        // Close statement
        $stmt->close();
    } else {
        // Log the error instead of echoing
        error_log("Error preparing SQL: " . $conn->error);
        // Redirect to an error page
        header('Location: error.php');
        exit;
    }

    // Redirect to manage group page
    header('Location: manage_groups.php?intake=' . $intake . '&semester=' . $semester . '&module=' . $module . '&project=' . $insert_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Group Assignment - Admin</title>
    <link rel="stylesheet" href="admin_submission.css" type="text/css">
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Create Group Assignment</h2>
        </div>
    </div>
    <div class="content-container">
        <div class="group-management">
            <h3>Group Assignment Created Successfully</h3>
            <a href="admin_dashboard.php">Go back to dashboard</a>
        </div>
    </div>
</body>
</html>

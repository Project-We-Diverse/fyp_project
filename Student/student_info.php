<?php

// * only start the session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'conn.php';

// * check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// * retrieve user ID from session
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} else {
    echo "User ID is not set in the session.";
    exit;
}

// * retrieve student ID from session
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} else {
    echo "student_id is not set in the session.";
    exit;
}

// Fetch user gender
$stmt = $conn->prepare('SELECT gender FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $gender = $user['gender'];
} else {
    echo "User not found.";
    exit;
}

      // Fetch and display student information assigned to the logged-in student
      $student_sql = "SELECT student_id, full_name 
      FROM students
      WHERE id = $student_id";
      

    $student_result = $conn->query($student_sql);

    if (!$student_result) {
        die('Query Error: ' . $conn->error);
    }
    
    $student_row = $student_result->fetch_assoc();

      ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bar.css" text="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
  <meta charset="UTF-8">
  <title>Students Information</title>
  <link rel="stylesheet" href="student_info.css">
</head>
<body>
<?php include "student_bar.php"; ?>
<div class="student_info_box">
  <h1>Students Information</h1>
      <div class="student-deets">
         <?php
        if ($student_row) 
            ?>
            <div class="student_info_container">
                <h2><strong><?php echo htmlspecialchars("Student Information"); ?></strong></h2>
                <hr class="divider">
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student_row["full_name"]); ?></p>
                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student_row["student_id"]); ?></p>
            </div>
            
    </tbody>
  </table>
</body>
</html>
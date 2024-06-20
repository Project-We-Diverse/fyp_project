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

// Get the ID of the logged-in user from the session
$user_id = $_SESSION['id'];

// Fetch the student_id from the students table based on user_id
$stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();

if (!$student_id) {
    die("Error: Student ID not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_assignment'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $submission_date = date('Y-m-d'); // Current date as submission date
    $file = $_FILES['file'];

    // Create the submissions directory if it doesn't exist
    $target_dir = "submissions/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if ($file['error'] == 0) {
        $document_path = $target_dir . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $document_path)) {
            // Prepare and execute the insert statement
            $stmt = $conn->prepare("INSERT INTO submissions (project_id, student_id, submission_title, submission_date, document_name, document_type, document_path, status, checked) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'unchecked')");
            $submission_title = pathinfo($file['name'], PATHINFO_FILENAME);
            $document_type = $file['type'];
            $stmt->bind_param('iisssss', $_POST['project_id'], $student_id, $submission_title, $submission_date, $name, $document_type, $document_path);

            if ($stmt->execute()) {
                echo "File uploaded and saved successfully.";
            } else {
                echo "Error saving file to database: " . $stmt->error;
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "Error uploading file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Assignment</title>
</head>
<body>
<form enctype="multipart/form-data" method="post" id="addForm" action="student_submit.php?project_id=<?php echo htmlspecialchars($_GET['project_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">                   
<table class="addForm" cellspacing="0">
  <tr>
    <th>Name:<span class="greenText">*</span></th>
    <td><input name="name" type="text" class="textBox required" value="Friendly Document Name" maxlength="80" required /></td>
  </tr>
  <tr>
    <th>Description:<span class="greenText">*</span></th>
    <td><textarea name="description" class="textBox required" required>Document description blah blah</textarea></td>
  </tr>
  <tr>
    <th>File:</th>
    <td><input type="file" name="file" class="textBox" required /></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td>
        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($_GET['project_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="submit" name="submit_assignment" class="button" value="Submit" />
    </td>
  </tr>
</table>
</form>
</body>
</html>
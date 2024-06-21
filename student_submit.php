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
                
                // Redirect to student_project_details.php with project_id
                $project_id = $_POST['project_id'];
                header("Location: student_project_details.php?project_id=$project_id");
                exit;
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
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            margin-left: 3px;
        }

        .textBox {
            width: 196%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        .button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #28a745;
            color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .button:hover {
            background-color: #008000;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <form enctype="multipart/form-data" method="post" id="addForm" action="student_submit.php?project_id=<?php echo htmlspecialchars($_GET['project_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <label for="name">Submission Title:</label>
            <input id="name" name="name" type="text" class="textBox required" maxlength="80" placeholder="Enter the file name here" required />
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="textBox required" maxlength="300" placeholder="Briefly explain about the file"></textarea>
            
            <label for="file">File:</label>
            <input id="file" type="file" name="file" class="textBox" required />
            
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($_GET['project_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
            
            <input type="submit" name="submit_assignment" class="button" value="Submit" />
        </form>
    </div>
</body>
</html>
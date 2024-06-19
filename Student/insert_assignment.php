<?php 
require_once 'student_submit.php';

$file = $_FILES['file']; // The uploaded file
$name = $_POST['name']; // The friendly document name
$description = $_POST['description']; // The document description
$submission_date = date('Y-m-d H:i:s'); // The current date and time
$document_type = $file['type']; // The type of the uploaded file
$assignment = new UploadFile();
$assignment->attach_file($file, $name, $description, $submission_date, $document_type);

if($assignment->save($file)){
    echo "<script>alert('Submitted Successfully!');</script>";

    // Assuming you're using an associative array to store the uploaded file
    $file = $_FILES['file']; // Replace 'file' with the name attribute of your file input

    if ($file['error'] === UPLOAD_ERR_OK) {
        // File uploaded successfully
        echo '<script>alert("File uploaded successfully.");</script>';
    } else {
        // File upload failed
        echo '<script>alert("File upload failed.");</script>';
    }
    
    // Redirect to student_dashboard
    header("Location: student_dashboard.php");
    exit(); // Make sure to exit after the redirect
}
?>
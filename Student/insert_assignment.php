<?php
require_once("class/student_submit.php");
if($_FILES['file']) {
    $assignment = new UploadFile();
    $assignment->attach_file($_FILES['main_picture'], $_POST['name'], $_POST['description']);
    if($pic->save()){
        echo "<script>alert('Submitted Successfully!');</script>";
    }
}
?>
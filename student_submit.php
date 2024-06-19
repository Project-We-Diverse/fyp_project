<?php

require 'conn.php';
class UploadFile{

//declare some variables in corresponding to your database field here, like fields, table name and stuffs

public function attach_file($file, $name, $description, $submission_date, $document_type) {
    if($file['error'] != 0) {
        //do something
    } else {
        $document_path    = $file['tmp_name'];
        $path_parts       = pathinfo($file['name']);
        $submission_title = ($file['name']); // to get the filename
        $document_type    = $file['type']; // to get the file type
        $this->document_name = $name;
        $this->description   = $description;
        $this->submission_date = $submission_date;
    }
}


public function save($file) {
    $target_path = "submissions/" . $file['name'];
    if(move_uploaded_file($file['tmp_name'], $target_path)) {
        // Connect to the database
        global $conn;
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO submissions (document_name, description, submission_date, document_type) VALUES (?, ?, ?, ?)");
        // Bind the parameters
        $stmt->bind_param('ssis', $this->document_name, $this->description, $this->submission_date, $this->document_type);        // Execute the statement
        if ($stmt->execute()) {
            // The file was successfully uploaded and saved in the database
            unset($this->file);
            return true;
        } else {
            // There was an error saving the file in the database
            return false;
        }
    } else {
        // There was an error moving the uploaded file
        return false;
    }
}

public function create() { 
    //Insert into database
    // Assuming you have a database connection established
    $query = "INSERT INTO submissions (submission_title, submission_date, document_type, document_name, document_name) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $this->file, $this->description);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
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
<form enctype="multipart/form-data" method="post" id="addForm" action="insert_assignment.php">                   
<table class="addForm" cellspacing="0">
  <tr>
    <th>Name:<span class="greenText">*</span></th>
    <td><input name="name" type="text" class="textBox required" value="Friendly Document Name" maxlength="80" /></td>
  </tr>
  <tr>
    <th>Description:<span class="greenText">*</span></th>
    <td><textarea name="description" class="textBox required">Document description blah blah</textarea></td>
  </tr>
  <tr>
    <th>File:</th>
    <td><input type="file" name = "file" class="textBox" /></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><input type="submit" class="button" value="Submit" /></td>
</tr>
</table>
</body>
</html>


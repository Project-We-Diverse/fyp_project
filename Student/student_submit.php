<?php

require 'conn.php';
class UploadFile{

//declare some variables in corresponding to your database field here, like fields, table name and stuffs

public function attach_file($file, $name, $description) {
    if($file['error'] != 0) {
        //do something
    } else {
        $studentfilepath    = $file['tmp_name'];
        $path_parts         = pathinfo($file['name']);
        $studentfilename     = $path_parts['extension'];// to get the filename
        $studentfiletype         = $file['type'];// to get the file type
        $studentfilesize         = $file['size'];// to get the size
        $this->studentfilename         = $name;
        $this->studentfiledescription  = $description;
    }
}


public function save() {        
    $target_path = "/some/folder";
    if(move_uploaded_file($this->studentfilename, $target_path)) {
        if($this->create()) {
            unset($this->studentfilename);
            return true;
        }
    } else {
        return false;
    }
}

public function create() { 
    //Insert into database
    // Assuming you have a database connection established
    $query = "INSERT INTO student_projects (student_filename, studentfiledescription, studentfilesize, studentfiletype) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $this->studentfilename, $this->studentfiledescription);
    
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
<form enctype="multipart/form-data" method="post" id="addForm" action="includes/insert_assignment.php">                   
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
    <td><input type="image" class="button" src="images/button_submit.gif" /></td>
  </tr>
</table>
</body>
</html>


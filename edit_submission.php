<?php

require 'conn.php';
class EditSubmission{
    public function update_file($file, $name, $description, $id) {
        if($file['error'] != 0) {
            // Handle error
        } else {
            $studentfilepath    = $file['tmp_name'];
            $path_parts         = pathinfo($file['name']);
            $studentfilename    = $path_parts['extension'];// to get the filename
            $studentfiletype    = $file['type'];// to get the file type
            $studentfilesize    = $file['size'];// to get the size
            $this->studentfilename         = $name;
            $this->studentfiledescription  = $description;
        }

        $target_path = "/some/folder";
        if(move_uploaded_file($this->studentfilename, $target_path)) {
            if($this->update($id)) {
                unset($this->studentfilename);
                return true;
            }
        } else {
            return false;
        }
    }

    public function update($id) { 
        // Update database
        $query = "UPDATE student_projects SET student_filename = ?, studentfiledescription = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $this->studentfilename, $this->studentfiledescription, $id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
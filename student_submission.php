<?php

require 'conn.php';
class UploadFile{}

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


<?php
require 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['notification_id'])) {
        $notification_id = $_POST['notification_id'];

        $sql = "DELETE FROM notifications WHERE project_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $notification_id);

        if ($stmt->execute()) {
            header("Location: student_notifications.php");
            exit;
        } else {
            echo "Error deleting notification: " . $conn->error;
        }
    }
}
?>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intake_name = $_POST['intake_name'];

    // Prepare and bind
    $stmt = $conn->prepare('INSERT INTO intakes (name) VALUES (?)');
    $stmt->bind_param('s', $intake_name);

    if ($stmt->execute()) {
        $intake_id = $stmt->insert_id;
        // Redirect to configure modules and semesters
        header('Location: configure_intake.php?intake_id=' . $intake_id);
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

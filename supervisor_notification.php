<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require database connection
require 'conn.php';
include 'supervisor_bar.php';

// Check if user is logged in and role is set
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role'])) {
    die('Access denied. Please log in.');
}

// Check if the logged-in user is a supervisor
if ($_SESSION['role'] !== 'supervisor') {
    die('Access denied. You must be a supervisor to view this page.');
}

$supervisor_id = $_SESSION['id'];

// Fetch projects supervised by the logged-in supervisor
$sql = "SELECT projects.id, projects.project_name, projects.end_date, intakes.name as intake_name, modules.name as module_name,
               notifications.notified
        FROM projects
        JOIN intakes ON projects.intake_id = intakes.id
        JOIN modules ON projects.module_id = modules.id
        LEFT JOIN notifications ON projects.id = notifications.project_id
        WHERE projects.supervisor_id = ?
        ORDER BY notifications.notified ASC, projects.end_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notify Students - Supervisor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .content-container {
            flex-grow: 1;
            padding: 20px;
            max-width: 1200px;
            margin-left: 11%;
        }

        h2 {
            color: #333;
        }

        .project-list {
            list-style-type: none;
            padding: 0;
        }

        .project-item {
            background-color: #fff;
            margin: 10px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            position: relative;
        }

        .project-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .project-item .title {
            font-weight: bold;
            font-size: 1.4em;
            margin-bottom: 10px;
        }

        .project-item .end-date {
            color: #e74c3c;
            font-weight: bold;
        }

        .project-item div {
            margin-bottom: 8px;
        }

        .project-item textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: none;
            margin-bottom: 10px;
            font-family: Arial, sans-serif;
            text-align: left; /* Ensure text aligns to the left */
        }

        .project-item .notify-button {
            background-color: #228b22;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .project-item .notify-button:hover {
            background-color: #006400;
        }

        .project-item .notify-button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .status-notified {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #28a745;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h2>Projects Under Your Supervision</h2>
        <ul class="project-list">
            <?php
            while ($row = $result->fetch_assoc()) {
                $project_id = $row['id']; // Store project ID for form submission
            ?>
                <li class="project-item">
                    <div class="title"><?php echo htmlspecialchars($row['project_name']); ?></div>
                    <div>End Date: <span class="end-date"><?php echo htmlspecialchars($row['end_date']); ?></span></div>
                    <div>Intake: <?php echo htmlspecialchars($row['intake_name']); ?></div>
                    <div>Module: <?php echo htmlspecialchars($row['module_name']); ?></div>
                    <?php if ($row['notified'] === 'notified'): ?>
                        <div class="status-notified">Notified</div>
                    <?php else: ?>
                        <form action="send_notification.php" method="POST" style="display:inline;">
                            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                            <textarea name="notification_message" placeholder="Enter your notification message here" rows="1" required><?php
                                echo htmlspecialchars("Just a reminder that your upcoming project, " . htmlspecialchars($row['project_name']) . " for " . htmlspecialchars($row['module_name']) . ", is due on " . htmlspecialchars($row['end_date']) . ". Please ensure you are on track with your progress. Feel free to reach out if you have any questions.");
                            ?></textarea>
                            <button type="submit" class="notify-button">Notify</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
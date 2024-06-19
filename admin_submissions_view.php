<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection file
require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

// Get project ID from URL
$projectId = $_GET['project_id'] ?? null;

// Function to get project details
function getProjectDetails($projectId) {
    require 'conn.php';
    $sql = "SELECT project_name FROM projects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $project;
}

// Function to get submissions for a specific project
function getSubmissions($projectId) {
    require 'conn.php';
    $submissions = [];
    $sql = "SELECT * FROM submissions WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $submissions;
}

// Function to update submission approval status
function updateSubmissionStatus($submissionId, $status) {
    require 'conn.php';
    $checked = 'checked';
    $sql = "UPDATE submissions SET status = ?, checked = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $status, $checked, $submissionId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Approve or reject submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id']) && isset($_POST['status'])) {
    $submissionId = $_POST['submission_id'];
    $status = $_POST['status'];
    updateSubmissionStatus($submissionId, $status);
}

$project = getProjectDetails($projectId);
$submissions = getSubmissions($projectId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions - Admin</title>
    <link rel="stylesheet" href="admin_submission_view.css" type="text/css">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>View Submission / Manage Submissions</h2>
        </div>
    </div>

    <div class="sidebar-container">
        <div class="sidebar-items">
            <ul>
                <li><a href="admin_dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i>Home</a></li>
                <li><a href="admin_student.php" class="sidebar-link"><i class="fa-solid fa-user"></i>Student</a></li>
                <li><a href="admin_supervisor.php" class="sidebar-link"><i class="fa-solid fa-user-tie"></i>Supervisor</a></li>
                <li><a href="admin_submission.php" class="sidebar-link"><i class="fa-solid fa-file"></i>Submission</a></li>
                <li><a href="admin_archived.php" class="sidebar-link"><i class="fa-solid fa-folder"></i>Manage Submission</a></li>
                <li class="logout"><a href="logout.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i>Log out</a></li>
            </ul>
        </div>
    </div>

    <div class="content-container">
        <h3>Submissions for Project: <?php echo htmlspecialchars($project['project_name']); ?> (Total: <?php echo count($submissions); ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>Submission ID</th>
                    <th>Title</th>
                    <th>Submission Date</th>
                    <th>Feedback to Student</th>
                    <th>Feedback to Admin</th>
                    <th>Document</th>
                    <th>Marks</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                <tr>
                    <td><?php echo htmlspecialchars($submission['id']); ?></td>
                    <td><?php echo htmlspecialchars($submission['submission_title']); ?></td>
                    <td><?php echo htmlspecialchars($submission['submission_date']); ?></td>
                    <td><?php echo htmlspecialchars($submission['feedback_to_student']); ?></td>
                    <td><?php echo htmlspecialchars($submission['feedback_to_admin']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($submission['document_path']); ?>"><?php echo htmlspecialchars($submission['document_name']); ?></a></td>
                    <td><?php echo htmlspecialchars($submission['marks']); ?></td>
                    <td><?php echo htmlspecialchars($submission['status']); ?></td>
                    <td>
                        <form action="admin_submissions_view.php?project_id=<?php echo htmlspecialchars($projectId); ?>" method="POST">
                            <input type="hidden" name="submission_id" value="<?php echo htmlspecialchars($submission['id']); ?>">
                            <button type="submit" name="status" value="approved">Approve</button>
                            <button type="submit" name="status" value="rejected">Reject</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

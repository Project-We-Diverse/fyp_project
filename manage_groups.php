<?php
session_start();

// Include database connection file
require 'conn.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.html');
    exit;
}

$intake = $_GET['intake'];
$semester = $_GET['semester'];
$module = $_GET['module'];
$project = $_GET['project'];

// Fetch students for the selected intake
$students = [];
$sql = "SELECT * FROM students WHERE intake_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $intake);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Fetch existing groups for the selected project
$groups = [];
$sql = "SELECT * FROM groups WHERE project_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $project);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $groups[] = $row;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_group') {
        $group_name = $_POST['group_name'];
        // Insert new group
        $sql = "INSERT INTO groups (group_name, project_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $group_name, $project);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] === 'add_student_to_group') {
        $group_id = $_POST['group_id'];
        $student_id = $_POST['student_id'];
        // Insert student into group
        $sql = "INSERT INTO group_members (group_id, student_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $group_id, $student_id);
        $stmt->execute();
        $stmt->close();
    }

    // Refresh the page to reflect changes
    header("Location: manage_groups.php?intake=$intake&semester=$semester&module=$module&project=$project");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Groups - Admin</title>
    <link rel="stylesheet" href="manage_groups.css" type="text/css">
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Manage Groups</h2>
        </div>
    </div>
    <div class="sidebar-container">
        <div class="sidebar-items">
            <ul>
                <li><a href="admin_dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i>Home</a></li> 
                <li><a href="admin_student.php" class="sidebar-link"><i class="fa-solid fa-user"></i>Student</a></li> 
                <li><a href="admin_supervisor.php" class="sidebar-link"><i class="fa-solid fa-user-tie"></i>Supervisor</a></li> 
                <li><a href="admin_submission.php" class="sidebar-link active"><i class="fa-solid fa-file"></i>Submission</a></li> 
                <li><a href="admin_archived.php" class="sidebar-link"><i class="fa-solid fa-folder"></i>Archived</a></li>
                <li class="logout"><a href="logout.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i>Log out</a></li>
            </ul>
        </div>
    </div>
    <div class="content-container">
        <div class="group-management">
            <h3>Create Group</h3>
            <form action="" method="POST">
                <input type="hidden" name="action" value="create_group">
                <label for="group_name">Group Name:</label>
                <input type="text" id="group_name" name="group_name" required>
                <button type="submit">Create Group</button>
            </form>

            <h3>Existing Groups</h3>
            <table class="group-table">
                <thead>
                    <tr>
                        <th>Group Name</th>
                        <th>Members</th>
                        <th>Add Members</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($group['group_name']); ?></td>
                            <td>
                                <?php
                                // Fetch group members
                                $group_members = [];
                                $sql = "SELECT * FROM students WHERE id IN (SELECT student_id FROM group_members WHERE group_id = ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('i', $group['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()) {
                                    $group_members[] = $row;
                                }
                                $stmt->close();
                                ?>
                                <ul>
                                    <?php foreach ($group_members as $member): ?>
                                        <li><?php echo htmlspecialchars($member['full_name']); ?> (<?php echo htmlspecialchars($member['student_id']); ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <form action="" method="POST">
                                    <input type="hidden" name="action" value="add_student_to_group">
                                    <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                    <label for="student_id">Select Student:</label>
                                    <select name="student_id" id="student_id" required>
                                        <option value="">--Select Student--</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['student_id']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Add to Group</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

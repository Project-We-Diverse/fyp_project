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

// Function to retrieve intakes from the database
function getIntakes() {
    require 'conn.php';
    $intakes = [];
    $sql = "SELECT * FROM intakes";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $intakes[] = $row;
        }
    }
    $conn->close();
    return $intakes;
}

// Function to retrieve semesters for a specific intake from the database
function getSemesters($intakeId) {
    require 'conn.php';
    $semesters = [];
    $sql = "SELECT * FROM semesters WHERE intake_id = $intakeId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $semesters[] = $row;
        }
    }
    $conn->close();
    return $semesters;
}

// Function to retrieve modules for a specific semester from the database
function getModules($semesterId) {
    require 'conn.php';
    $modules = [];
    $sql = "SELECT * FROM modules WHERE semester_id = $semesterId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
    }
    $conn->close();
    return $modules;
}

// Function to retrieve projects for a specific module from the database
function getProjects($moduleId, $archive = false) {
    require 'conn.php';
    $projects = [];
    $statusCondition = $archive ? "AND status = 'completed'" : "";
    $sql = "SELECT * FROM projects WHERE module_id = $moduleId $statusCondition";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
    }
    $conn->close();
    return $projects;
}

// Update project details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_details'])) {
    require 'conn.php';
    $projectId = $_POST['project_id'];
    $project_name = $_POST['project_name'];
    $description = $_POST['description'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare('UPDATE projects SET project_name = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?');
    $stmt->bind_param('sssssi', $project_name, $description, $startDate, $endDate, $status, $projectId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Delete project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    require 'conn.php';
    $projectId = $_POST['project_id'];

    // Delete related rows in the group_members table first
    $stmt = $conn->prepare('DELETE FROM group_members WHERE group_id IN (SELECT id FROM groups WHERE project_id = ?)');
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $stmt->close();

    // Delete related rows in the groups table
    $stmt = $conn->prepare('DELETE FROM groups WHERE project_id = ?');
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $stmt->close();

    // Delete related rows in the submissions table
    $stmt = $conn->prepare('DELETE FROM submissions WHERE project_id = ?');
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $stmt->close();

    // Delete the project
    $stmt = $conn->prepare('DELETE FROM projects WHERE id = ?');
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Initialize variables
$intakes = getIntakes();
$selectedIntake = $_GET['intake'] ?? null;
$semesters = $selectedIntake ? getSemesters($selectedIntake) : [];
$archive = isset($_GET['archive']) ? true : false;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Submission / - Admin</title>
    <link rel="stylesheet" href="admin_archived.css" type="text/css">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2><?php echo $archive ? 'Archive' : 'Manage Submission'; ?></h2>           
        </div>
    </div>

    <div class="sidebar-container">
        <div class="sidebar-items">
            <ul>
                <li><a href="admin_dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i>Home</a></li> 
                <li><a href="admin_student.php" class="sidebar-link"><i class="fa-solid fa-user"></i>Student</a></li> 
                <li><a href="admin_supervisor.php" class="sidebar-link"><i class="fa-solid fa-user-tie"></i>Supervisor</a></li> 
                <li><a href="admin_submission.php" class="sidebar-link"><i class="fa-solid fa-file"></i>Submission</a></li> 
                <li><a href="admin_archived.php" class="sidebar-link active"><i class="fa-solid fa-folder"></i>Manage Submission</a></li>
                <li class="logout"><a href="logout.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i>Log out</a></li>
            </ul>
        </div>
    </div>

    <div class="content-container">
        <!-- Step 1: Select Intake -->
        <div class="archived-step" id="step-intake">
            <h3>Select Intake</h3>
            <form action="admin_archived.php" method="GET">
                <label for="intake">Select Intake:</label>
                <select id="intake" name="intake" required>
                    <?php foreach ($intakes as $intake): ?>
                        <option value="<?php echo $intake['id']; ?>" <?php echo ($selectedIntake == $intake['id']) ? 'selected' : ''; ?>>
                            <?php echo $intake['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($archive): ?>
                    <input type="hidden" name="archive" value="true">
                <?php endif; ?>
                <button type="submit">Next</button>
            </form>
        </div>
        <!-- Step 2: Display Semesters, Modules, and Projects -->
        <?php if ($selectedIntake): ?>
            <?php if (!$archive): ?>
                <button class="archive" onclick="window.location.href='admin_archived.php?archive=true'" style="float: right;">View Archive</button>
            <?php endif; ?>    
        <?php foreach ($semesters as $index => $semester): ?>
            <div class="semester-container">
                <h2>Semester <?php echo $index + 1; ?></h2>
                <?php $modules = getModules($semester['id']); ?>
                <?php foreach ($modules as $module): ?>
                    <div class="module-container">
                        <h4>Module: <?php echo htmlspecialchars($module['name']); ?></h4>
                        <?php $projects = getProjects($module['id'], $archive); ?>
                        <div class="projects-grid">
                            <?php foreach ($projects as $project): ?>
                                <?php
                                    // Determine the status bar class based on the status
                                    $statusBarClass = $project['status'] == 'completed' ? 'completed' : 'in_progress';
                                ?>
                                <div class="project-box" data-project='<?php echo json_encode($project); ?>' onclick="openEditModal(this)">
                                    <div class="project-title"><?php echo htmlspecialchars($project['project_name']); ?></div>
                                    <div class="project-status">Status: <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($project['status']))); ?></div>
                                    <div class="project-dates">Start: <?php echo htmlspecialchars($project['start_date']); ?><br>End: <?php echo htmlspecialchars($project['end_date']); ?></div>
                                    <div class="project-status-bar <?php echo $statusBarClass; ?>"></div>
                                    <div class="view-submissions-link">
                                        <a href="admin_submissions_view.php?project_id=<?php echo $project['id']; ?>">View Submissions</a>
                                    </div>
                                    <!-- Add link to manage groups only for group projects -->
                                    <?php if ($project['is_group_project']): ?>
                                    <div class="manage-groups-link">
                                        <a href="manage_groups.php?intake=<?php echo $selectedIntake; ?>&semester=<?php echo $semester['id']; ?>&module=<?php echo $module['id']; ?>&project=<?php echo $project['id']; ?>">Manage Groups</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <form id="editForm" action="admin_archived.php" method="POST">
            <input type="hidden" name="project_id" id="modalProjectId">
                <label for="modalProjectName">Project Name:</label>
                <input type="text" id="modalProjectName" name="project_name" required>
                <label for="modalDescription">Description:</label>
                <textarea id="modalDescription" name="description" required></textarea>
                <label for="modalStartDate">Start Date:</label>
                <input type="date" id="modalStartDate" name="start_date" required>
                <label for="modalEndDate">End Date:</label>
                <input type="date" id="modalEndDate" name="end_date" required>
                <label for="modalStatus">Status:</label>
                <select id="modalStatus" name="status" required>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                <button type="submit" name="update_details">Update Details</button>
                <button type="submit" name="delete_project" onclick="return confirmDelete();" style="background-color: red; color: white;">Delete Project</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(element) {
            var project = JSON.parse(element.getAttribute('data-project'));
            document.getElementById('modalProjectId').value = project.id;
            document.getElementById('modalProjectName').value = project.project_name;
            document.getElementById('modalDescription').value = project.description;
            document.getElementById('modalStartDate').value = project.start_date;
            document.getElementById('modalEndDate').value = project.end_date;
            document.getElementById('modalStatus').value = project.status || 'in_progress';
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function confirmDelete() {
            return confirm("All submissions and groups created for this project will be deleted. Do you want to proceed?");
        }
    </script>
</body>
</html>

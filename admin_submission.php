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
    } else {
        echo "No modules found for semester with ID: $semesterId"; // Debug output
    }
    $conn->close();
    return $modules;
}

// Initialize variables
$intakes = getIntakes();
$selectedIntake = $_GET['intake'] ?? null;
$selectedSemester = $_GET['semester'] ?? null;
$selectedModule = $_GET['module'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission - Admin</title>
    <link rel="stylesheet" href="admin_submission.css" type="text/css">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Create Assignment</h2>
        </div>
    </div>

    <div class="sidebar-container">
        <div class="sidebar-items">
            <ul>
                <li><a href="admin_dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i>Home</a></li> 
                <li><a href="admin_student.php" class="sidebar-link"><i class="fa-solid fa-user"></i>Student</a></li> 
                <li><a href="admin_supervisor.php" class="sidebar-link active"><i class="fa-solid fa-user-tie"></i>Supervisor</a></li> 
                <li><a href="admin_submission.php" class="sidebar-link"><i class="fa-solid fa-file"></i>Submission</a></li> 
                <li><a href="admin_archived.php" class="sidebar-link"><i class="fa-solid fa-folder"></i>Manage Submission</a></li>
                <li class="logout"><a href="logout.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i>Log out</a></li>
            </ul>
        </div>
    </div>

    <div class="content-container">
        <!-- Step 1: Select Intake -->
        <div class="submission-step" id="step-intake">
            <h3>Select Intake</h3>
            <form action="admin_submission.php" method="GET">
                <label for="intake">Select Intake:</label>
                <select id="intake" name="intake" required>
                    <?php foreach ($intakes as $intake): ?>
                        <option value="<?php echo $intake['id']; ?>" <?php echo ($selectedIntake == $intake['id']) ? 'selected' : ''; ?>>
                            <?php echo $intake['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Next</button>
            </form>
        </div>

        <!-- Step 2: Select Semester -->
        <?php if ($selectedIntake): ?>
            <div class="submission-step" id="step-semester">
                <h3>Select Semester</h3>
                <form action="admin_submission.php" method="GET">
                    <input type="hidden" name="intake" value="<?php echo $selectedIntake; ?>">
                    <label for="semester">Semester:</label>
                    <select id="semester" name="semester" required>
                        <?php $semesters = getSemesters($selectedIntake); ?>
                        <?php foreach ($semesters as $semester): ?>
                            <option value="<?php echo $semester['id']; ?>" <?php echo ($selectedSemester == $semester['id']) ? 'selected' : ''; ?>>
                                <?php echo $semester['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Next</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Step 3: Select Module and Define Assignment -->
        <?php if ($selectedSemester): ?>
            <div class="submission-step" id="step-module">
                <h3>Select Module and Define Assignment</h3>
                <form id="module-form" action="admin_submission.php" method="GET">
                    <input type="hidden" name="intake" value="<?php echo $selectedIntake; ?>">
                    <input type="hidden" name="semester" value="<?php echo $selectedSemester; ?>">
                    <label for="module">Module:</label>
                    <select id="module" name="module" required>
                        <?php $modules = getModules($selectedSemester); ?>
                        <?php foreach ($modules as $module): ?>
                            <option value="<?php echo $module['id']; ?>" <?php echo ($selectedModule == $module['id']) ? 'selected' : ''; ?>>
                                <?php echo $module['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="assignment_type">Assignment Type:</label>
                    <select id="assignment_type" name="assignment_type" required>
                        <option value="individual">Individual</option>
                        <option value="group">Group</option>
                    </select>
                    <button type="submit">Next</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Step 4: Enter Assignment Details for Individual -->
        <?php if ($selectedModule && isset($_GET['assignment_type']) && $_GET['assignment_type'] === 'individual'): ?>
            <div class="submission-step" id="step-assignment-details">
                <h3>Assignment Details</h3>
                <form id="assignment-form" action="create_assignment.php" method="POST">
                    <input type="hidden" name="intake" value="<?php echo $selectedIntake; ?>">
                    <input type="hidden" name="semester" value="<?php echo $selectedSemester; ?>">
                    <input type="hidden" name="module" value="<?php echo $selectedModule; ?>">
                    <input type="hidden" name="assignment_type" value="individual">
                    <label for="assignment_title">Title:</label>
                    <input type="text" id="assignment_title" name="title" maxlength="50" require>
                    <label for="assignment_description">Description:</label>
                    <textarea id="assignment_description" name="description" maxlength="200" required></textarea>
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" required>
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" required>
                    <button type="submit">Submit</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Step 4: Manage Groups for Group Assignment -->
        <?php if ($selectedModule && isset($_GET['assignment_type']) && $_GET['assignment_type'] === 'group'): ?>
            <div class="submission-step" id="step-group-details">
                <h3>Create Groups</h3>
                <form id="group-form" action="create_group_assignment.php" method="POST">
                    <input type="hidden" name="intake" value="<?php echo $selectedIntake; ?>">
                    <input type="hidden" name="semester" value="<?php echo $selectedSemester; ?>">
                    <input type="hidden" name="module" value="<?php echo $selectedModule; ?>">
                    <input type="hidden" name="assignment_type" value="group">
                    <label for="group_title">Group Assignment Title:</label>
                    <input type="text" id="group_title" name="title" maxlength="50" required>
                    <label for="group_description">Description:</label>
                    <textarea id="group_description" name="description" maxlength="200" required></textarea>
                    <label for="group_start_date">Start Date:</label>
                    <input type="date" id="group_start_date" name="start_date" required>
                    <label for="group_end_date">End Date:</label>
                    <input type="date" id="group_end_date" name="end_date" required>
                    <button type="submit">Create Groups</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>

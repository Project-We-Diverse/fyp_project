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

if (!isset($_GET['intake_id'])) {
    echo "No intake ID provided.";
    exit;
}

$intake_id = $_GET['intake_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['module_name']) && isset($_POST['semester_id'])) {
        $module_name = $_POST['module_name'];
        $semester_id = $_POST['semester_id']; // Get semester_id from the form
        $stmt = $conn->prepare('INSERT INTO modules (name, intake_id, semester_id) VALUES (?, ?, ?)'); // Include semester_id in the query
        $stmt->bind_param('sii', $module_name, $intake_id, $semester_id); // Bind semester_id
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['semester_name'])) {
        $semester_name = $_POST['semester_name'];
        $stmt = $conn->prepare('INSERT INTO semesters (name, intake_id) VALUES (?, ?)');
        $stmt->bind_param('si', $semester_name, $intake_id);
        $stmt->execute();
        $stmt->close();
    }
}


$modules = [];
$semesters = [];

// Fetch existing modules
$stmt = $conn->prepare('SELECT * FROM modules WHERE intake_id = ?');
$stmt->bind_param('i', $intake_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $modules[] = $row;
}
$stmt->close();

// Fetch existing semesters
$stmt = $conn->prepare('SELECT * FROM semesters WHERE intake_id = ?');
$stmt->bind_param('i', $intake_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $semesters[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Intake - Admin</title>
    <link rel="stylesheet" href="admin_bar.css" type="text/css">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Configure Intake</h2>
        </div>
    </div>

    <div class="sidebar-container">
        <div class="sidebar-items">
            <ul>
                <li><a href="admin_dashboard.php" class="sidebar-link active"><i class="fa-solid fa-house"></i>Home</a></li> 
                <li><a href="admin_student.php" class="sidebar-link"><i class="fa-solid fa-user"></i>Student</a></li> 
                <li><a href="admin_supervisor.php" class="sidebar-link"><i class="fa-solid fa-user-tie"></i>Supervisor</a></li> 
                <li><a href="admin_submission.php" class="sidebar-link"><i class="fa-solid fa-file"></i>Submission</a></li> 
                <li><a href="admin_archived.php" class="sidebar-link"><i class="fa-solid fa-folder"></i>Archived</a></li>
                <li class="logout"><a href="logout.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i>Log out</a></li>
            </ul>
        </div>
    </div>

    <div class="content-container">
        <div class="intakes-section">
            <h3>Modules for Intake</h3>

            <form action="" method="POST">
                <label for="module_name">Module Name:</label>
                <input type="text" id="module_name" name="module_name" required>
                <label for="semester_select">Select Semester:</label>
                <select name="semester_id" id="semester_select">
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo $semester['id']; ?>"><?php echo htmlspecialchars($semester['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Add Module</button>
            </form>

            <h3>Semesters for Intake</h3>
            <ul>
            <?php foreach ($semesters as $semester): ?>
                <h3><?php echo htmlspecialchars($semester['name']); ?></h3>
                <ul>
                    <?php foreach ($modules as $module): ?>
                        <?php if ($module['semester_id'] == $semester['id']): ?>
                            <li><?php echo htmlspecialchars($module['name']); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>

            </ul>

            <form action="" method="POST">
                <label for="semester_name">Semester:</label>
                <input type="text" id="semester_name" name="semester_name" required>
                <button type="submit">Add Semester</button>
            </form>
        </div>
    </div>
    
</body>
</html>

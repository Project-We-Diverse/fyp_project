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

// * retrieve user ID from session
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} else {
    echo "User ID is not set in the session.";
    exit;
}

// Fetch user gender
$stmt = $conn->prepare('SELECT gender FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $gender = $user['gender'];
} else {
    echo "User not found.";
    exit;
}

$stmt->close();

// Fetch existing intakes
$intakeStmt = $conn->prepare('SELECT * FROM intakes ORDER BY created_at DESC');
$intakeStmt->execute();
$intakeResult = $intakeStmt->get_result();
$intakes = [];
while ($row = $intakeResult->fetch_assoc()) {
    $intakes[] = $row;
}
$intakeStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Admin</title>
    <link rel="stylesheet" href="admin_bar.css" text="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Admin Dashboard</h2>
        </div>

        <div class="profile">
            <a href="admin_profile.php">
                <?php if ($gender == "male"): ?>
                    <img src="assets/male.png" alt="Male Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php else: ?>
                    <img src="assets/female.png" alt="Female Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;">
                <?php endif; ?>
            </a>
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
        <div class="intakes-section">
            <h3>Manage Intakes</h3>

            <!-- Display existing intakes -->
            <div class="existing-intakes">
                <h4>Existing Intakes</h4>
                <ul>
                <?php foreach ($intakes as $intake): ?>
                        <li><a href="configure_intake.php?intake_id=<?php echo $intake['id']; ?>"><?php echo htmlspecialchars($intake['name']); ?> </a> (Started: <?php echo htmlspecialchars(date("Y-m-d", strtotime($intake['created_at']))); ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Form to create new intake -->
            <div class="create-intake">
                <h4>Create New Intake</h4>
                <form action="create_intake.php" method="POST">
                    <label for="intake-name">Intake Name:</label>
                    <input type="text" id="intake-name" name="intake_name" required>
                    <button type="submit">Create Intake</button>
                </form>
            </div>
        </div>
    </div>
    
</body>
</html>

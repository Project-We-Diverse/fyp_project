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

// Fetch existing intakes
$intakes = [];
$stmt = $conn->prepare('SELECT * FROM intakes');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $intakes[] = $row;
}
$stmt->close();

$selected_intake_id = $_GET['intake_id'] ?? null;
$students = [];

if ($selected_intake_id) {
    // Fetch students for the selected intake
    $stmt = $conn->prepare('SELECT * FROM students WHERE intake_id = ?');
    $stmt->bind_param('i', $selected_intake_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $student_id = $_POST['student_id'];
        $full_name = $_POST['full_name'];
        $intake_id = $_POST['intake_id'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert into users table
            $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            $role = 'student';
            $stmt->bind_param('sss', $username, $password, $role);
            $stmt->execute();
            $user_id = $stmt->insert_id;
            $stmt->close();

            // Insert into students table
            $stmt = $conn->prepare('INSERT INTO students (user_id, username, password, intake_id, student_id, full_name) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ississ', $user_id, $username, $password, $intake_id, $student_id, $full_name);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }

    } elseif ($action === 'update') {
        $id = $_POST['primary_id']; // This should be the actual primary key of the student record
        $student_id = $_POST['student_id']; // This is the student's ID card number
        $username = $_POST['username'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
        $full_name = $_POST['full_name'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Get the current username and user_id of the student
            $stmt = $conn->prepare('SELECT username, user_id FROM students WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($current_username, $user_id);
            $stmt->fetch();
            $stmt->close();

            // Update the students table
            if ($password) {
                $stmt = $conn->prepare('UPDATE students SET username = ?, password = ?, student_id = ?, full_name = ? WHERE id = ?');
                $stmt->bind_param('ssssi', $username, $password, $student_id, $full_name, $id);
            } else {
                $stmt = $conn->prepare('UPDATE students SET username = ?, student_id = ?, full_name = ? WHERE id = ?');
                $stmt->bind_param('sssi', $username, $student_id, $full_name, $id);
            }
            $stmt->execute();
            $stmt->close();

            // Update the users table
            if ($password) {
                $stmt = $conn->prepare('UPDATE users SET username = ?, password = ? WHERE id = ? AND role = ?');
                $role = 'student';
                $stmt->bind_param('ssis', $username, $password, $user_id, $role);
            } else {
                $stmt = $conn->prepare('UPDATE users SET username = ? WHERE id = ? AND role = ?');
                $role = 'student';
                $stmt->bind_param('sis', $username, $user_id, $role);
            }

            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }

    } elseif ($action === 'delete') {
        $student_id = $_POST['student_id'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Get the username and user_id of the student to be deleted
            $stmt = $conn->prepare('SELECT username, user_id FROM students WHERE id = ?');
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $stmt->bind_result($username, $user_id);
            $stmt->fetch();
            $stmt->close();

            // Delete from the group_members table first
            $stmt = $conn->prepare('DELETE FROM group_members WHERE student_id = ?');
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $stmt->close();

            // Delete from the students table
            $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $stmt->close();

            // Delete from the users table
            $stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND role = ?');
            $role = 'student';
            $stmt->bind_param('is', $user_id, $role);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
    }

    // Refresh the page to reflect changes
    header("Location: admin_student.php?intake_id=" . $intake_id);
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin</title>
    <link rel="stylesheet" href="admin_bar.css" type="text/css">
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Manage Students</h2>
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
        <div class="student-management">
            <h3>Select Intake</h3>
            <form action="admin_student.php" method="GET">
                <label for="intake_id">Select Intake:</label>
                <select name="intake_id" id="intake_id" required>
                    <option value="">--Select Intake--</option>
                    <?php foreach ($intakes as $intake): ?>
                        <option value="<?php echo $intake['id']; ?>" <?php echo ($selected_intake_id == $intake['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($intake['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">View Students</button>
            </form>
            <?php if ($selected_intake_id): ?>
                <h3>Students in Intake: <?php echo htmlspecialchars($intakes[array_search($selected_intake_id, array_column($intakes, 'id'))]['name']); ?></h3>

                <div class="form-container">
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="intake_id" value="<?php echo $selected_intake_id; ?>">
                        <label for="student_id">Student ID:</label>
                        <input type="text" id="student_id" name="student_id" maxlength="8" required>
                        <label for="full_name">Full Name:</label>
                        <input type="text" id="full_name" name="full_name" maxlength="100" required>
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" maxlength="15">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" maxlength="20" required>
                        <button type="submit">Add Student</button>
                    </form>
                </div>

                <h3>Existing Students</h3>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <input type="hidden" value="<?php echo $student['id']; ?>"> <!-- Hidden primary key -->
                                        <button class="edit-button" onclick="editStudent(<?php echo $student['id']; ?>)">Edit</button>
                                        <form action="" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <input type="hidden" name="intake_id" value="<?php echo $selected_intake_id; ?>">
                                            <button type="submit" class="delete-button">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="form-container" id="edit-student-form" style="display: none;">
                    <h3>Edit Student</h3>
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" id="edit-primary-id" name="primary_id">
                        <input type="hidden" name="intake_id" value="<?php echo $selected_intake_id; ?>">
                        <label for="edit-student-id">Student ID:</label>
                        <input type="text" id="edit-student-id" name="student_id" maxlength="8" required>
                        <label for="edit-full-name">Full Name:</label>
                        <input type="text" id="edit-full-name" name="full_name" maxlength="100" required>
                        <label for="edit-username">Username:</label>
                        <input type="text" id="edit-username" name="username" maxlength="15" required>
                        <label for="edit-password">Password (leave blank to keep current):</label>
                        <input type="password" id="edit-password" name="password" maxlength="20">
                        <button type="submit">Update Student</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function editStudent(studentId) {
            // Fetch student data from the table
            const row = document.querySelector(`tr td input[value="${studentId}"]`).closest('tr');
            const primaryId = studentId;
            const studentIdCard = row.querySelector('td:nth-child(1)').textContent.trim();
            const fullName = row.querySelector('td:nth-child(2)').textContent.trim();
            const username = row.querySelector('td:nth-child(3)').textContent.trim();

            // Populate the edit form with fetched data
            document.getElementById('edit-primary-id').value = primaryId;
            document.getElementById('edit-student-id').value = studentIdCard;
            document.getElementById('edit-full-name').value = fullName;
            document.getElementById('edit-username').value = username;
            document.getElementById('edit-password').value = '';

            // Show the edit form
            document.getElementById('edit-student-form').style.display = 'block';
        }
    </script>
</body>
</html>


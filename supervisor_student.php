<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information - Supervisor</title>
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .title {
            text-align: left;
            margin-left: 12%;
            margin-top: 27px;
            font-size: 23px;
        }

        table {
            border-collapse: collapse;
            width: 85%;
            margin-left: 12%;
            border: 0.5px solid #c0c0c0;
        }

        th, td {
            border: 0.5px solid #c0c0c0;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include "supervisor_bar.php"; ?>
    <h1 class="title">Student Information</h1>
    <table>
        <tr>
            <th>Student ID</th>
            <th>Full Name</th>
            <th>Intake</th>
            <th>Status</th>
        </tr>
        <?php
        include "conn.php";

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get the ID of the logged-in supervisor from the session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $supervisor_id = $_SESSION['id'];

        $sql = "SELECT students.student_id, students.full_name, intakes.name AS intake_name, students.status 
                FROM students 
                JOIN intakes ON students.intake_id = intakes.id
                WHERE intakes.supervisor_id = $supervisor_id
                ORDER BY students.student_id ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["student_id"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["intake_name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No students found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>
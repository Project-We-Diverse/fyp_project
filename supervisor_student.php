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
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-left: 15%;
            margin-right: 0;
            margin-top: 2.3%;
        }

        .title {
            text-align: left;
            font-size: 24px;
            padding: 20px;
            background-color: #0056b3;
            color: #fff;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .graduated {
            color: #28a745;
        }

        .dropped-out {
            color: #dc3545; 
        }

        .active {
            color: #007bff; 
        }
    </style>
</head>
<body>
    <?php include "supervisor_bar.php"; ?>
    <div class="container">
        <h1 class="title">Student Information</h1>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Intake</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
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
                        ORDER BY CASE 
                                    WHEN students.status = 'Active' THEN 1
                                    WHEN students.status = 'Graduated' THEN 2
                                    WHEN students.status = 'Dropped out' THEN 3
                                    ELSE 4
                                END,
                                students.student_id ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["student_id"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["intake_name"]) . "</td>";
                        echo "<td class='" . strtolower(str_replace(' ', '-', htmlspecialchars($row["status"]))) . "'>" . htmlspecialchars($row["status"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No students found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
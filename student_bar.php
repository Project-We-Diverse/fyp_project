<?php
include "conn.php";

session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userID'];

$sql = "SELECT gender FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($gender);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Student</title>
    <link rel="stylesheet" href="bar.css" text="text/css">
    <link rel="icon" href="assets/favicon.png" text="image/png">
    <script src="https://kit.fontawesome.com/d9960a92ff.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-dashboard">
            <h2>Supervisor Dashboard</h2>
        </div>

        <div class="profile">
            <a href="student_profile.php">
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
                <li><a href="#" class="sidebar-link active"><i class="fa-solid fa-house"></i>Home</a></li> // * change # to the file path of Home
                <li><a href="#" class="sidebar-link"><i class="fa-solid fa-file"></i>Submission</a></li> // * change # to the file path of Submission
                <li class="logout"><a href="logout.php" id="logout-link"><i class="fa-solid fa-right-from-bracket"></i>Log out</a></li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarLink = document.querySelectorAll(".sidebar-link");

            sidebarLink.forEach(function(link) {
                link.addEventListener("click", function(event) {
                    event.preventDefault();
                    sidebarLink.forEach(function(link) {
                        link.classList.remove("active");
                    });
                    this.classList.add("active");
                })
            })
        })

        const logoutLink = document.getElementById("logout-link");

        logoutLink.addEventListener("click", function() {
            event.preventDefault();

            const confirmed = confirm("Are you sure you want to log out?");

            if (confirmed) {
                window.location.href = "logout.php";
            } else {
                alert("Logout cancelled.")
            }
        })
    </script>
</body>
</html>
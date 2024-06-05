<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form id="loginForm">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>

    <script>
        document.getElementById("loginForm").addEventListener("submit", function(event) {
            event.preventDefault();
            // Get the entered username and password
            const username = document.getElementById("username").value;
            const password = document.getElementById("password").value;
            
            // Perform login logic
            login(username, password);
        });

        function login(username, password) {
            // Here you can make an AJAX request to your server to authenticate the user
            // For demonstration purposes, let's just log the credentials
            console.log("Username: " + username);
            console.log("Password: " + password);
            
            // After successful login, you can redirect the user to another page
            // window.location.href = "dashboard.html";
        }
    </script>
</body>
</html>

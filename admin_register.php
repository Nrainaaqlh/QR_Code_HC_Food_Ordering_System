<?php
include 'db.php';

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Retrieve data from the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminEmail = $con->real_escape_string($_POST['adminEmail']);
    $adminUsername = $con->real_escape_string($_POST['adminUsername']);
    $adminPassword = $_POST['adminPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($adminPassword !== $confirmPassword) {
        echo '<script>alert("Passwords do not match."); window.location.href = "admin_register.php";</script>';
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    // Prepare SQL statement to insert data into the 'admins' table
    $stmt = $con->prepare("INSERT INTO admins (adminEmail, adminUsername, adminPassword) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $adminEmail, $adminUsername, $hashedPassword);

    if ($stmt->execute()) {
        echo '<script>alert("New record created successfully ✅"); window.location.href = "admin_login.php";</script>';
    } else {
        echo '<script>alert("Error: Could not register. Please try again."); window.location.href = "admin_register.php";</script>';
    }

    // Close statement and database connection
    $stmt->close();
    $con->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Register</title>
    <style>
        body { 
            background-color: #411900;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        #register-container {
            background-color: #fff;
            box-shadow: 10px 10px 10px 10px rgba(5, 5, 10, 0.1);
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        #register-container img {
            width: 300px; 
            height: 150px; 
            object-fit: cover; 
            margin-top: 0;
        }

        #register-container h1 {
            color: black;
        }

        #register-form {
            margin-top: 20px;
        }

        #register-form label {
            display: block;
            margin-bottom: 8px;
            margin-top: 15px;
            text-align: left;
            font-weight: bold;
            color: #555;
        }

        #register-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #register-form button {
            background-color: #411900;
            color: #fff;
            width: 100%;
            padding: 10px 15px;
            margin-top: 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        #register-form button:hover {
            background-color: #613316;
        }

        @media screen and (max-width: 400px) {
            #register-container {
                width: 80%;
            }
        }
    </style>
</head>
<body>
    <div id="register-container">
        <img src="logo.png" alt="logo">
        <h1>Register</h1>
        <form id="register-form" action="admin_register.php" method="post">
            <label for="adminEmail">Email:</label>
            <input type="email" id="adminEmail" name="adminEmail" required><br>

            <label for="adminUsername">Username:</label>
            <input type="text" id="adminUsername" name="adminUsername" required><br>

            <label for="adminPassword">Password:</label>
            <input type="password" id="adminPassword" name="adminPassword" required><br>

            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required><br>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>

<?php
session_start();
include 'db.php';

$error = ""; 

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminUsername = $con->real_escape_string($_POST['adminUsername']);
    $adminPassword = $_POST['adminPassword'];

    // Prepare and execute the query to fetch the hashed password
    $stmt = $con->prepare("SELECT adminPassword FROM admins WHERE adminUsername = ?");
    $stmt->bind_param("s", $adminUsername);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify the provided password against the hashed password
    if ($hashedPassword && password_verify($adminPassword, $hashedPassword)) {
        $_SESSION['loggedin'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['loggedin'] = false;
        $error = "Invalid username or password.";
    }
}

$con->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <style>
        body { 
            background-color: #411900;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        #login-container {
            background-color: #fff;
            box-shadow: 10px 10px 10px 10px rgba(5, 5, 10, 0.1);
            width: 300px;
            margin: 80px auto;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        #login-container img{
            margin-top: 0px;
            width: 300px; 
            height: 150px; 
            object-fit: cover; 
            margin-top: 0px;
        }

        #login-container h1 {
            color: black;
            margin-top: 0px;
        }

        #login-container p {
            margin-top: 5px;
            font-size: 14px;
        }

        #forgot{
            text-align: right;
        }
        
        #login-form {
            margin-top: 20px;
        }

        #login-form label {
            display: block;
            margin-bottom: 8px;
            text-align: left;
            font-weight: bold;
            color: #555;
        }

        #login-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($error)): ?>
        #login-form input {
            border-bottom: 1px solid red;
        }

        #login-form .error-icon {
            display: block;
        }
        <?php endif; ?>

        #login-form button {
            background-color: #411900;
            color: #fff;
            width: 100%;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        #login-form button:hover {
            background-color: #613316;
        }

        #error-messages {
            text-align:left;
            font-size: 13px;
            margin-top: 0;
            padding: 0;
            color: red;
        }

        @media screen and (max-width: 400px) {
            #login-container {
                width: 80%;
            }
        }
    </style>
</head>
<body >
    <div id="login-container" class="login-container">
    <img src="logo.png" alt="logo">

    <h1>Login</h1>

    <form id="login-form" action="admin_login.php" method="post">
        <label for="adminUsername">Username:</label>
        <input type="text" id="adminUsername" name="adminUsername" required><br>

        <label for="adminPassword">Password:</label>
        <input type="password" id="adminPassword" name="adminPassword" required><br>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($error)): ?>
            <div id="error-messages" class="error-messages">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>
        <p id="forgot"><a href="admin_forgotPassword.php">Forgot Password?</a></p>
        <button type="submit">Login</button>
    </form>
    
</div>
</body>
</html>

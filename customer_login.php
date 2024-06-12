<?php
session_start();
include 'db.php';

$error = "";

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $custUsername = $_POST['custUsername'];
    $custPhoneNum = $_POST['custPhoneNum'];

    // Prepare the statement to prevent SQL injection
    $stmt = $con->prepare("SELECT * FROM customers WHERE custPhoneNum = ?");
    $stmt->bind_param("s", $custPhoneNum);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, log them in
        $row = $result->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['custID'] = $row['custID'];
        $_SESSION['custName'] = $row['custName'];
        
        // Flash message for successful login
        $_SESSION['flash_message'] = "Login successful. Welcome, " . $row['custName'] . "!";

        // Redirect to homepage after successful login
        header("Location: customer_homepage.php");
        exit();
    } else {
        $error = "No account found with this phone number.";
    }

    $stmt->close();
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="customer_style.css">
    <style>
        body {
            background-image: url('wallpaper2.avif'); 
            background-size: cover;
            background-position: center;
            color: #4e342e;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .login-container {
            background-color: #ffffff;
            width: 100%;
            height: 90%;
            max-width: 400px;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }

        .login-container img {
            width: 300px;
            height: 150px;
            border-radius: 16px 16px 0 0;
            object-fit: cover;
            margin: 0 auto;
        }

        .login-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: black;
        }

        #login-form label {
            display: block;
            margin-bottom: 8px;
            text-align: left;
            font-weight: bold;
            color: black;
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            box-sizing: border-box;
            border: 1px solid #411900;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .login-form input::placeholder {
            color: #999;
        }

        #login-form button {
            background-color: #411900;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            margin-bottom: 20px;
            width: 100%;
            font-size: 16px;
        }

        #login-form button:hover {
            background-color: #613316;
        }

        .login-form .error-messages {
            text-align: left;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
            color: red;
        }

        .login-form .additional-links a:hover {
            text-decoration: underline;
        }

        .close-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #411900;
        }

        @media screen and (max-width: 400px) {
            .login-container {
                margin-top: 0 auto;
                width: 90%;
                padding: 10px;
            }

            .login-form input, .login-form button {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <button class="close-button" onclick="window.location.href='customer_homepage.php'">&times;</button>
        <img src="logo.png" alt="Header Image">
        <h1>Login</h1>
        <form id="login-form" action="" method="post">
            <label for="custUsername">Name:</label>
            <input type="text" id="custUsername" name="custUsername" required><br>

            <label for="custPhoneNum">Phone Number:</label>
            <input type="text" id="custPhoneNum" name="custPhoneNum" required><br>

            <?php if ($error) { ?>
                <div class="error-messages"><?php echo $error; ?></div>
            <?php } ?>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

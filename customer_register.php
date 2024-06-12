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
    $custEmail = $_POST['custEmail'];

    $stmt = $con->prepare("SELECT * FROM customers WHERE custPhoneNum = ?");
    $stmt->bind_param("s", $custPhoneNum);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Phone number is already registered.";
    } else {
        $stmt->close();
        $stmt = $con->prepare("INSERT INTO customers (custName, custPhoneNum, custEmail) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $custUsername, $custPhoneNum, $custEmail);
        $stmt->execute();

        $newCustomerId = $con->insert_id;

        $_SESSION['loggedin'] = true;
        $_SESSION['custID'] = $newCustomerId;
        $_SESSION['custName'] = $custUsername;

        $stmt->close();
        $con->close();

        $_SESSION['flash_message'] = "Register successful. Welcome, " . $_SESSION['custName'] . "!";

        header("Location: customer_homepage.php");
        exit();
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
    <title>Register Page</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            position: relative;
            background-color: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
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
            margin-bottom: 10px;
            color: black;
        }

        #login-form label {
            display: block;
            margin-top: 5px;
            margin-bottom: 8px;
            text-align: left;
            font-weight: bold;
            color: black;
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
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
            width: 100%;
            font-size: 16px;
        }

        #login-form button:hover {
            background-color: #613316;
        }

        .login-form .error-messages {
            text-align: left;
            font-size: 14px;
            margin-bottom: 10px;
            color: red;
        }

        .login-form .additional-links {
            margin-top: 10px;
        }

        .login-form .additional-links a {
            text-decoration: none;
            color: #411900;
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
                width: 90%;
                padding: 10px;
            }

            .login-form input,
            .login-form button {
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
        <h1>Register</h1>
        <form id="login-form" action="" method="post" onsubmit="return validatePhoneNumber()">
            <label for="custUsername">Name:</label>
            <input type="text" id="custUsername" name="custUsername" required><br>

            
            <label for="custPhoneNum">Phone Number:</label>
            <input type="text" id="custPhoneNum" name="custPhoneNum" required><br>

            <label for="custEmail">Email:</label>
            <input type="email" id="custEmail" name="custEmail" required><br>

            <?php if ($error) { ?>
                <div class="error-messages"><?php echo $error; ?></div>
            <?php } ?>
            <span id="phone-error-message" class="error-messages"></span>
            <button type="submit">Register</button>
        </form>
    </div>
    <script>
        function validatePhoneNumber() {
            var phoneNumber = document.getElementById("custPhoneNum").value;
            var errorElement = document.getElementById("phone-error-message");

            if (phoneNumber.length < 8 || phoneNumber.length > 15) {
                errorElement.textContent = "Phone number must be between 8 to 15 digits.";
                return false;
            }

            errorElement.textContent = ""; // Clear any previous error message
            return true;
        }
    </script>
</body>
</html>

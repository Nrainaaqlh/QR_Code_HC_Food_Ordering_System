<?php
session_start();
require 'db.php'; // Include your database connection script

$message = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $stmt = $con->prepare("SELECT * FROM admins WHERE resetToken = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, show reset password form
        if (isset($_POST['reset'])) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password === $confirm_password) {
                // Hash the new password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Update the password in the admins table
                $user = $result->fetch_assoc();
                $email = $user['adminEmail'];

                $stmt = $con->prepare("UPDATE admins SET adminPassword = ?, resetToken = NULL WHERE adminEmail = ?");
                $stmt->bind_param("ss", $hashedPassword, $email);
                if ($stmt->execute()) {
                    $message = "Your password has been reset successfully.";
                    // Redirect to the login page after successful password reset
                    header("Location: admin_login.php");
                    exit();
                } else {
                    $error = "Error updating password. Please try again.";
                }
            } else {
                $error = "Passwords do not match.";
            }
        }
    } else {
        $error = "Invalid or expired token.";
    }
} else {
    $error = "No token provided.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            background-color: #411900;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        #reset-password-container {
            background-color: #fff;
            box-shadow: 10px 10px 10px 10px rgba(5, 5, 10, 0.1);
            width: 300px;
            margin: 80px auto;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }


        #reset-password-container img {
            margin-top: 0px;
            width: 300px; 
            height: 150px; 
            object-fit: cover; 
            margin-top: 0px;
        }

        #reset-password-container  h2 {
            text-align: center;
            margin-top: 0px;
        }

        #reset-password-container label {
            display: block;
            margin-bottom: 8px;
            text-align: left;
            font-weight: bold;
            color: #555;
        }

        #reset-password-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #reset-password{
            margin-top: 20px;
        }

        button {
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

        button:hover {
            background-color: #613316;
        }

        .message {
            text-align: left;
            font-size: 13px;
            margin-top: 0;
            padding: 0;
            color: green;
        }

        .error {
            text-align: left;
            font-size: 13px;
            margin-top: 0;
            padding: 0;
            color: red;
        }
    </style>
</head>
<body>
<div id="reset-password-container">
        <img src="logo.png" alt="logo">
        <h2>Reset Password</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if (isset($token) && $result->num_rows > 0): ?>
        <form id="reset-password" action="admin_resetPassword.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" required><br>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br> 
            <button type="submit" name="reset">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
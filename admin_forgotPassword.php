<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Required files
require_once 'PHP mailer/phpmailer/src/Exception.php';
require_once 'PHP mailer/phpmailer/src/PHPMailer.php';
require_once 'PHP mailer/phpmailer/src/SMTP.php';
require_once 'db.php'; // Update with your actual database connection file

$error = ''; // Initialize the error variable

if (isset($_POST["send"])) {

    $email = $_POST["email"];

    // Check if the email exists in the admins table
    $stmt = $con->prepare("SELECT * FROM admins WHERE adminEmail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a token for the password reset link
        $token = bin2hex(random_bytes(32));

        // Store the token in the admins table
        $stmt = $con->prepare("UPDATE admins SET resetToken = ? WHERE adminEmail = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Send the reset email
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();                              // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';         // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                     // Enable SMTP authentication
            $mail->Username   = 'ainarul19@gmail.com';    // SMTP username
            $mail->Password   = 'jwkvqktftflgaiay';       // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit SSL encryption
            $mail->Port       = 465;                      // TCP port to connect to

            // Recipients
            $mail->setFrom('ainarul19@gmail.com', 'HC Cafe'); // Sender email and name
            $mail->addAddress($email); // Add a recipient email

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = 'Click on the following link to reset your password: <a href="http://localhost/project_PSM/admin_resetPassword.php?token=' . $token . '">Reset Password</a>';

            // Send the email
            $mail->send();
            $message = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email address not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body {
            background-color: #411900;
            font-family: 'Arial', sans-serif;
            margin-top: 0;
            margin-left: 0;
            padding: 0;
            overflow-x: hidden;
        }
        #forgot-password-container {
            background-color: #fff;
            box-shadow: 10px 10px 10px 10px rgba(5, 5, 10, 0.1);
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        #forgot-password-container img{
            width: 300px; 
            height: 150px; 
            object-fit: cover; 
            margin-top: 0px;
        }

        #forgot-password-container h2 {
            color: black;
        }
        #forgot-password label{
            display: block;
            margin-bottom: 8px;
            margin-top:15px;
            text-align: left;
            font-weight: bold;
            color: #555;
        }
        
        #forgot-password input {
            width: 100%;
            padding: 10px;
            margin-bottom: 0px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($error)): ?>
        #forgot-password input {
            border-bottom: 1px solid red;
        }

        #forgot-password .error-icon {
            display: block;
        }
        <?php endif; ?>

        #forgot-password button {
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
        #forgot-password button:hover {
            background-color: #613316;
        }
        .message {
            text-align:left;
            font-size: 13px;
            margin top: 0;
            padding: 0;
            color: green;
        }
        .error {
            text-align:left;
            font-size: 13px;
            margin top: 0;
            padding: 0;
            color: red;
        }
        /* Styling for back button */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .back-button a {
            text-decoration: none;
            color: #411900;
            font-size: 16px;
            padding: 8px 12px;
            background-color: #fff;
            border: 1px solid #411900;
            border-radius: 4px;
        }
        .back-button a:hover {
            background-color: #411900;
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="forgot-password-container">
        <div class="back-button">
            <a href="admin_login.php">&lt; Back</a>
        </div>
        <img src="logo.png" alt="logo">
        <h2>Forgot Password</h2>
        <form id="forgot-password" action="admin_forgotPassword.php" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (isset($message)) { echo '<p class="message">' . $message . '</p>'; } ?>

            <button type="submit" name="send">Submit</button>
        </form>
    </div>
</body>
</html>

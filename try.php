<?php
// Establish connection to the database
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "contact_db";

// Create connection
$con = new mysqli('localhost', 'root', '', 'contact_db');

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Retrieve data from the form
$registrationDate = $con->real_escape_string($_POST['registrationDate']);
$firstName = $con->real_escape_string($_POST['firstName']);
$lastName = $con->real_escape_string($_POST['lastName']);
$icNum = $con->real_escape_string($_POST['icNum']);
$phoneNum = $con->real_escape_string($_POST['phoneNum']);
$email = $con->real_escape_string($_POST['email']);
$username = $con->real_escape_string($_POST['username']);
$password = $con->real_escape_string($_POST['password']);
$confirmPassword = $con->real_escape_string($_POST['confirmPassword']);
$role = $con->real_escape_string($_POST['role']);

// Check if passwords match
if ($password !== $confirmPassword) {
    echo '<script>alert("Passwords do not match."); window.location.href = "register.php";</script>';
    exit();
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare SQL statement to insert data into the 'users' table
$sql = "INSERT INTO users (registrationDate, firstName, lastName, icNum, phoneNum, email, username, password, role) VALUES ('$registrationDate', '$firstName', '$lastName', '$icNum', '$phoneNum', '$email', '$username', '$hashedPassword', '$role')";

if ($con->query($sql) === TRUE) {
    echo '<script>alert("New record created successfully ✅"); window.location.href = "login2.php";</script>';
} else {
    echo "Error: " . $sql . "<br>" . $con->error;
}

// Close the database connection
$con->close();
?>
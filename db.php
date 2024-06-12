<?php
$host = "localhost";
$db_name = "project_psm";
$username = "root";
$password = "";

$con = mysqli_connect('localhost','root','','project_psm');

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

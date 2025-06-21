<?php
// Database connection configuration
$host = "localhost";
$user = "root";
$pass = "";
$db = "contact_db1";

// Establish database connection
$con = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
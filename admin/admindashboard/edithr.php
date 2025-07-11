<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: login/login.php");
    exit();
}

// DB connection
$servername = "localhost";
$username = "root"; // your DB username
$password = "";     // your DB password
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve posted data
$employee_number = $conn->real_escape_string($_POST['employee_number']);
$first_name = $conn->real_escape_string($_POST['first_name']);
$middle_name = $conn->real_escape_string($_POST['middle_name']);
$last_name = $conn->real_escape_string($_POST['last_name']);
$address = $conn->real_escape_string($_POST['address']);
$email = $conn->real_escape_string($_POST['email']);

// Update query
$sql = "UPDATE hr_admin SET first_name='$first_name', middle_name='$middle_name', last_name='$last_name', address='$address', email='$email' WHERE employee_number='$employee_number'";

if ($conn->query($sql) === TRUE) {
    header("Location: dashboard.php");
    exit();
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>

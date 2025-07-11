<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: login/login.php");
    exit();
}

// Check if employee_number is provided
if (!isset($_GET['employee_number'])) {
    die("Employee number not provided.");
}

// Database connection
$servername = "localhost";
$username = "root"; // your DB username
$password = "";     // your DB password
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize employee_number
$employee_number = $conn->real_escape_string($_GET['employee_number']);

// Delete query
$sql = "DELETE FROM hr_admin WHERE employee_number = '$employee_number'";

if ($conn->query($sql) === TRUE) {
    header("Location: admindashboard.php"); // redirect back to dashboard after delete
    exit();
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>

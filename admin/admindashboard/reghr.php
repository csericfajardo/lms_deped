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

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert HR account when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_number = $_POST['hremployee_no'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepared statement for safety
    $stmt = $conn->prepare("INSERT INTO hr_admin (hremployee_no, first_name, last_name, address, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $employee_number, $first_name, $last_name, $address, $email, $password);

    if ($stmt->execute()) {
        header("Location: admindashboard.php");
        exit();
    } else {
        error_log("DB Insert Error: " . $stmt->error);
        echo "An unexpected error occurred. Please try again later.";
    }

    $stmt->close();
}

$conn->close();
?>

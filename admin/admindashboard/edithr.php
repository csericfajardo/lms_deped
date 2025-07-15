<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: login/login.php");
    exit();
}

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve posted data safely
$hremployee_no = $_POST['hremployee_no'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$middle_name = $_POST['middle_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$address = $_POST['address'] ?? '';
$email = $_POST['email'] ?? '';

// Validate required fields before update
if ($hremployee_no && $first_name && $middle_name && $last_name && $address && $email) {
    $stmt = $conn->prepare("UPDATE hr_admin SET first_name=?, middle_name=?, last_name=?, address=?, email=? WHERE hremployee_no=?");
    $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $address, $email, $hremployee_no);

    if ($stmt->execute()) {
        header("Location: admindashboard.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Missing required fields.";
}

$conn->close();
?>

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

// Retrieve and validate hremployee_no
$hremployee_no = $_GET['hremployee_no'] ?? '';

if ($hremployee_no) {
    // Use prepared statement for security
    $stmt = $conn->prepare("DELETE FROM hr_admin WHERE hremployee_no = ?");
    $stmt->bind_param("s", $hremployee_no);

    if ($stmt->execute()) {
        header("Location: admindashboard.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request. No employee number provided.";
}

$conn->close();
?>

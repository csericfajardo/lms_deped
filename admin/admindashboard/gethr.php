<?php
session_start();

header("Content-Type: application/json");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    echo json_encode(["error" => "Not authorized"]);
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
    echo json_encode(["error" => "Connection failed"]);
    exit();
}

if (!isset($_GET['hremployee_no'])) {
    echo json_encode(["error" => "No employee_number provided"]);
    exit();
}

$hremployee_no = $conn->real_escape_string($_GET['hremployee_no']);
$sql = "SELECT * FROM hr_admin WHERE hremployee_no = '$hremployee_no'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "HR account not found"]);
}

$conn->close();
?>

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

if (!isset($_GET['employee_number'])) {
    echo json_encode(["error" => "No employee_number provided"]);
    exit();
}

$employee_number = $conn->real_escape_string($_GET['employee_number']);
$sql = "SELECT * FROM hr_admin WHERE employee_number = '$employee_number'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "HR account not found"]);
}

$conn->close();
?>

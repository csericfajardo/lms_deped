<?php
// updateemployee.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employee_no = $_POST['employee_no'];
$last_name = $_POST['last_name'];
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$position = $_POST['position'];
$station = $_POST['station'];
$email = $_POST['email'];

$sql = "UPDATE tb_employee 
        SET last_name=?, first_name=?, middle_name=?, position=?, station=?, email=?
        WHERE employee_no=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $last_name, $first_name, $middle_name, $position, $station, $email, $employee_no);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>

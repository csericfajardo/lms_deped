<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employee_no = $_POST['employee_no'];
$position = $_POST['position'];
$station = $_POST['station'];
$email = $_POST['email'];

$sql = "UPDATE tb_employee SET position='$position', station='$station', email='$email' WHERE employee_no='$employee_no'";
if ($conn->query($sql) === TRUE) {
    echo "Updated successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

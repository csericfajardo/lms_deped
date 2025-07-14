<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$employee_number = $_GET['employee_number'];

// Fetch employee personal details
$sql = "SELECT employee_no, last_name, first_name, middle_name, position, station, email FROM tb_employee WHERE employee_no = '$employee_number'";
$result = $conn->query($sql);
$employee = $result->fetch_assoc();

// Fetch leave details from tb_leaves
$sql2 = "SELECT vacation_leave, mandatory_leave, sick_leave, other_leave, otherleave_count FROM tb_leaves WHERE employee_no = '$employee_number'";
$result2 = $conn->query($sql2);
$leaves = $result2->fetch_assoc();

// Handle no leave record gracefully
if (!$leaves) {
    $leaves = [
        "vacation_leave" => "0",
        "mandatory_leave" => "0",
        "sick_leave" => "0",
        "other_leave" => "",
        "otherleave_count" => ""
    ];
}

// Return as JSON
echo json_encode([
    "employee" => $employee,
    "leaves" => $leaves
]);

$conn->close();
?> 
<?php 
// db connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve one employee for sample dashboard view
$sql = "SELECT employee_no, last_name, first_name, middle_name, position, station FROM tb_employee LIMIT 1";
$result = $conn->query($sql);
$employee = $result->fetch_assoc();

// Initialize leaves array
$leaves = [
    'vacation_leave' => 0,
    'mandatory_leave' => 0,
    'sick_leave' => 0
];

// If employee found, get their leaves
if ($employee) {
    $emp_no = $employee['employee_no'];
    $sql_leaves = "SELECT vacation_leave, mandatory_leave, sick_leave FROM tb_leaves WHERE employee_no = '$emp_no' LIMIT 1";
    $result_leaves = $conn->query($sql_leaves);

    if ($result_leaves && $result_leaves->num_rows > 0) {
        $leaves = $result_leaves->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Employee Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
            background: #f0f2f5;
        }
        .header {
            background: #1565c0;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .logout-btn {
            background: #ef5350;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: #d32f2f;
        }
        .container {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin: 30px auto;
            max-width: 800px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .details h2 {
            margin-top: 0;
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .details p {
            margin: 8px 0;
            font-size: 16px;
        }
        .leaves {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .leave-box {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .leave-box:hover {
            transform: translateY(-3px);
        }
        .leave-box h3 {
            margin: 0;
            font-size: 18px;
            color: #1565c0;
        }
        .leave-box p {
            margin: 12px 0 0 0;
            font-size: 24px;
            font-weight: bold;
            color: #0d47a1;
        }
        @media (max-width: 600px) {
            .leaves {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Employee Dashboard</h1>
    <form action="logout.php" method="POST">
    <button type="submit" class="logout-btn">Logout</button>
</form>
</div>

<div class="container details">
    <h2>Employee Details</h2>
    <?php if ($employee) { ?>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['last_name'] . ", " . $employee['first_name'] . " " . $employee['middle_name']); ?></p>
        <p><strong>Employee Number:</strong> <?php echo htmlspecialchars($employee['employee_no']); ?></p>
        <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
        <p><strong>Station:</strong> <?php echo htmlspecialchars($employee['station']); ?></p>
    <?php } else { ?>
        <p>No employee data found.</p>
    <?php } ?>
</div>

<div class="container leaves">
    <div class="leave-box">
        <h3>Vacation Leave</h3>
        <p><?php echo htmlspecialchars($leaves['vacation_leave']); ?></p>
    </div>
    <div class="leave-box">
        <h3>Mandatory Leave</h3>
        <p><?php echo htmlspecialchars($leaves['mandatory_leave']); ?></p>
    </div>
    <div class="leave-box">
        <h3>Sick Leave</h3>
        <p><?php echo htmlspecialchars($leaves['sick_leave']); ?></p>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>

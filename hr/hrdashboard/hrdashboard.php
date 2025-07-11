<?php
session_start();

// Prevent caching of dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: login/login.php");
    exit();
}

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve Employee Accounts from tb_employee
$sql = "SELECT employee_no, last_name, first_name, middle_name, position, station, email FROM tb_employee";
$result = $conn->query($sql);

// Retrieve form data and errors if any
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$form_errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboardstye.css" />
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo $_SESSION['admin_email']; ?></h2>
        <form action="logout.php" method="POST" style="display:inline; float: right;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>

        <div class="tabs">
            <button class="tab active" onclick="showTab('employee_accounts', event)">Employee Accounts</button>
            <button class="tab" onclick="showTab('logs', event)">Logs</button>
        </div>

        <div id="employee_accounts" class="tab-content active">
            <div id="employeeAccountsSection">
                <h3>Employee Accounts
                    <button class="add-employee-btn" onclick="showAddEmployeeForm()">Add Employee</button>
                </h3>
                <table>
                    <tr>
                        <th>Employee Number</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Station</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>

                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $full_name = $row["last_name"] . ", " . $row["first_name"] . ", " . $row["middle_name"];
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["employee_no"]) . "</td>";
                            echo "<td>" . htmlspecialchars($full_name) . "</td>";
                            echo "<td>" . htmlspecialchars($row["position"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["station"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                            echo "<td>";
                            echo "<button onclick=\"editEmployee('" . $row["employee_no"] . "')\">Edit</button> ";
                            echo "<button onclick=\"deleteEmployee('" . $row["employee_no"] . "')\">Delete</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No employee accounts found.</td></tr>";
                    }
                    ?>
                </table>
            </div>

            <div class="add-employee-form" id="addEmployeeForm" style="display:<?php echo !empty($form_errors) ? 'block' : 'none'; ?>;">
                <button class="close-btn" onclick="closeAddEmployeeForm()">×</button>
                <h4>Add Employee</h4>
                <form action="regemployees.php" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                    <h5>Personal Details</h5>

                    <label for="employee_no">Employee Number:</label>
                    <input type="text" id="employee_no" name="employee_no" placeholder="Employee Number" required value="<?php echo isset($form_data['employee_no']) ? htmlspecialchars($form_data['employee_no']) : ''; ?>">
                    <?php if(isset($form_errors['employee_no'])) echo "<label style='color:red;'>".$form_errors['employee_no']."</label>"; ?>

                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" placeholder="First Name" required value="<?php echo isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : ''; ?>">

                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" value="<?php echo isset($form_data['middle_name']) ? htmlspecialchars($form_data['middle_name']) : ''; ?>">

                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" required value="<?php echo isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : ''; ?>">

                    <label for="position">Position:</label>
                    <input type="text" id="position" name="position" placeholder="Position" required value="<?php echo isset($form_data['position']) ? htmlspecialchars($form_data['position']) : ''; ?>">

                    <label for="station">Station:</label>
                    <input type="text" id="station" name="station" placeholder="Station" required value="<?php echo isset($form_data['station']) ? htmlspecialchars($form_data['station']) : ''; ?>">

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Email" required value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                    <?php if(isset($form_errors['email'])) echo "<label style='color:red;'>".$form_errors['email']."</label>"; ?>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>

                    <hr>

                    <h5>Leave Credits</h5>
                    <label for="vacation_leave">Vacation Leave Credits:</label>
                    <input type="number" id="vacation_leave" name="vacation_leave" placeholder="Vacation Leave Credits" min="0" value="<?php echo isset($form_data['vacation_leave']) ? htmlspecialchars($form_data['vacation_leave']) : '0'; ?>" required>

                    <label for="mandatory_leave">Mandatory Leave Credits:</label>
                    <input type="number" id="mandatory_leave" name="mandatory_leave" placeholder="Mandatory Leave Credits" min="0" value="<?php echo isset($form_data['mandatory_leave']) ? htmlspecialchars($form_data['mandatory_leave']) : '0'; ?>" required>

                    <label for="sick_leave">Sick Leave Credits:</label>
                    <input type="number" id="sick_leave" name="sick_leave" placeholder="Sick Leave Credits" min="0" value="<?php echo isset($form_data['sick_leave']) ? htmlspecialchars($form_data['sick_leave']) : '0'; ?>" required>

                    <button type="submit" style="margin-top: 10px;">Submit</button>
                </form>
            </div>
        </div>

        <div id="logs" class="tab-content">
            <h3>Logs</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Action</th>
                    <th>Date</th>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Admin logged in</td>
                    <td>2025-07-09 08:00</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Employee account created</td>
                    <td>2025-07-08 14:30</td>
                </tr>
            </table>
        </div>
    </div>

<script>
    function showTab(tabId, event) {
        var contents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < contents.length; i++) {
            contents[i].classList.remove('active');
        }

        var tabs = document.getElementsByClassName('tab');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('active');
        }

        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function showAddEmployeeForm() {
        document.getElementById('employeeAccountsSection').style.display = 'none';
        document.getElementById('addEmployeeForm').style.display = 'block';
    }

    function closeAddEmployeeForm() {
        document.getElementById('addEmployeeForm').style.display = 'none';
        document.getElementById('employeeAccountsSection').style.display = 'block';
    }

    function deleteEmployee(employeeNumber) {
        if (confirm("Are you sure you want to delete this employee account?")) {
            window.location.href = "deleteemployee.php?employee_number=" + employeeNumber;
        }
    }

    function editEmployee(employeeNumber) {
        // Similar AJAX logic for edit functionality can be implemented here
    }
</script>
</body>
</html>

<?php
// Clear form data and errors after displaying
unset($_SESSION['form_data']);
unset($_SESSION['form_errors']);
$conn->close();
?>

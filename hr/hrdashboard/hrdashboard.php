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
    <style>
        
       

    </style>
</head>
<body>
<div class="dashboard-container">
   <h2>
  Welcome, <?php echo $_SESSION['admin_email']; ?>
  <?php if (isset($_SESSION['hremployee_no'])) { ?>
    (<span id="loggedEmployeeNo"><?php echo htmlspecialchars($_SESSION['hremployee_no']); ?></span>)
  <?php } ?>
</h2>
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
                        echo "<button onclick=\"viewEmployee('" . $row["employee_no"] . "')\">View</button> ";
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

        <!-- View Employee Form Pane -->
       <div class="view-employee-form" id="viewEmployeeForm">
        <button class="back-btn" onclick="closeViewEmployeeForm()" style="background:none;border:none;font-size:18px;cursor:pointer;">← Back</button>
        <h4>View Employee</h4>
        <div id="employeeDetails"></div>
        <hr class="separator">
        <div id="leaveDetails"></div>
    </div>

        <!-- Add Employee Form -->
        <div class="add-employee-form" id="addEmployeeForm" style="display:<?php echo !empty($form_errors) ? 'block' : 'none'; ?>;">
            <button class="close-btn" onclick="closeAddEmployeeForm()">×</button>
            <h4>Add Employee</h4>
            <form action="regemployees.php" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                <h5>Personal Details</h5>

                <label for="employee_no">Employee Number:</label>
                <input type="text" id="employee_no" name="employee_no" required value="<?php echo isset($form_data['employee_no']) ? htmlspecialchars($form_data['employee_no']) : ''; ?>">
                <?php if(isset($form_errors['employee_no'])) echo "<label style='color:red;'>".$form_errors['employee_no']."</label>"; ?>

                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required value="<?php echo isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : ''; ?>">

                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name" value="<?php echo isset($form_data['middle_name']) ? htmlspecialchars($form_data['middle_name']) : ''; ?>">

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required value="<?php echo isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : ''; ?>">

                <label for="position">Position:</label>
                <input type="text" id="position" name="position" required value="<?php echo isset($form_data['position']) ? htmlspecialchars($form_data['position']) : ''; ?>">

                <label for="station">Station:</label>
                <input type="text" id="station" name="station" required value="<?php echo isset($form_data['station']) ? htmlspecialchars($form_data['station']) : ''; ?>">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                <?php if(isset($form_errors['email'])) echo "<label style='color:red;'>".$form_errors['email']."</label>"; ?>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <hr>

                <h5>Leave Credits</h5>
                <label for="vacation_leave">Vacation Leave Credits:</label>
                <input type="number" id="vacation_leave" name="vacation_leave" min="0" value="<?php echo isset($form_data['vacation_leave']) ? htmlspecialchars($form_data['vacation_leave']) : '0'; ?>" required>

                <label for="mandatory_leave">Mandatory Leave Credits:</label>
                <input type="number" id="mandatory_leave" name="mandatory_leave" min="0" value="<?php echo isset($form_data['mandatory_leave']) ? htmlspecialchars($form_data['mandatory_leave']) : '0'; ?>" required>

                <label for="sick_leave">Sick Leave Credits:</label>
                <input type="number" id="sick_leave" name="sick_leave" min="0" value="<?php echo isset($form_data['sick_leave']) ? htmlspecialchars($form_data['sick_leave']) : '0'; ?>" required>

                <button type="submit" style="margin-top: 10px;">Submit</button>
            </form>
        </div>
    </div>

<!-- Leave Application Modal -->
<div id="leaveModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeLeaveModal()">&times;</span>
    <h4>Apply for Leave</h4>
    <form id="leaveForm" action="applyleave.php" method="POST">
      <input type="hidden" name="employee_no" id="modal_employee_no">
      <input type="hidden" name="type_of_leave" id="modal_type_of_leave">
      
      <p>Type of Leave: <strong id="modal_leave_type_display"></strong></p>
      
      <label>No of Days:</label>
      <input type="number" name="no_of_days" min="1" required><br>
      <label>Inclusive Dates:</label>
      <input type="text" name="inclusive_dates" required><br>
      <label>Details of Leave:</label>
      <input type="text" name="details_of_leave" required><br>
      <label>Date of Application:</label>
      <input type="date" name="date_of_application" required><br>
      
     <button type="button" onclick="submitLeaveApplication()">Submit</button>

    </form>
  </div>
</div>

<!-- Add Leave Credit Modal -->
<div id="addLeaveCreditModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeAddLeaveCreditModal()">&times;</span>
    <h4>Add Leave Credit</h4>
    <form id="addLeaveCreditForm" action="addleavecredit.php" method="POST">
  <input type="hidden" name="employee_no" id="add_leave_employee_no">
  <input name="hremployee_no" id="hremployee_no" value="<?php echo isset($_SESSION['hremployee_no']) ? htmlspecialchars($_SESSION['hremployee_no']) : ''; ?>">
  
  <input name="leave_type" id="add_leave_leave_type">

  <label>Leave Count:</label>
  <input type="number" name="leave_count" min="1" required><br>

  <label>Leave Details:</label>
  <input type="text" name="leave_details" required><br>

  <label>Leave Expiration:</label>
  <input type="date" name="leave_expiration" required><br>

  <button type="button" onclick="submitAddLeaveCredit()">Submit</button>
</form>

  </div>
</div>


<script src="hrdashboard_script.js"></script>
</body>
</html>

<?php
unset($_SESSION['form_data']);
unset($_SESSION['form_errors']);
$conn->close();
?>

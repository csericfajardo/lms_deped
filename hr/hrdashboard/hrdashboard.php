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
        .view-employee-form {
            display: none;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        .leave-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            background: #fff;
        }
        hr.separator {
            border: 1px solid #666;
            margin: 20px 0;
        }
        .edit-actions {
    display: inline-flex;
    border: 1px solid #ccc;
    border-radius: 4px;
    overflow: hidden;
    margin-left: 10px;
}

.edit-actions button {
    border: none;
    padding: 5px 10px;
    background: #f0f0f0;
    cursor: pointer;
}

.edit-actions button:hover {
    background: #ddd;
}

.edit-actions .divider {
    width: 1px;
    background: #ccc;
}

.delete-btn {
    background: none;
    border: none;
    color: red;
    font-size: 20px;
    cursor: pointer;
    float: right;
}

.modal {
    display: none; 
    position: fixed; 
    z-index: 999; 
    left: 0; top: 0; 
    width: 100%; height: 100%;
    overflow: auto; 
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
}

    </style>
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
            <button class="close-btn" onclick="closeViewEmployeeForm()">×</button>
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

function closeViewEmployeeForm() {
    document.getElementById('viewEmployeeForm').style.display = 'none';
    document.getElementById('employeeAccountsSection').style.display = 'block';
}

function deleteEmployee(employeeNumber) {
    if (confirm("Are you sure you want to delete this employee account?")) {
        window.location.href = "deleteemployee.php?employee_number=" + employeeNumber;
    }
}

function viewEmployee(employeeNumber) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "viewemployee.php?employee_number=" + employeeNumber, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            console.log(data); // Debug

            if (data.error) {
                alert("Error: " + data.error);
                return;
            }

            if (!data.employee) {
                alert("Employee data not found.");
                return;
            }

            var details = "<h5>Personal Details</h5>";
            details += "<p><strong>Employee No:</strong> " + data.employee.employee_no + "</p>";
            details += "<p><strong>Name:</strong> " + data.employee.last_name + ", " + data.employee.first_name + ", " + data.employee.middle_name + "</p>";
            details += "<p><strong>Position:</strong> " + data.employee.position + "</p>";
            details += "<p><strong>Station:</strong> " + data.employee.station + "</p>";
            details += "<p><strong>Email:</strong> " + data.employee.email + "</p>";
            document.getElementById('employeeDetails').innerHTML = details;

            // Leave credits processing remains unchanged...
            var leaves = "<h5>Leave Credits</h5>";
            leaves += "<div class='leave-box'><strong>Vacation Leave:</strong> " + data.leaves.vacation_leave + "</div>";
            leaves += "<div class='leave-box'><strong>Mandatory Leave:</strong> " + data.leaves.mandatory_leave + "</div>";
            leaves += "<div class='leave-box'><strong>Sick Leave:</strong> " + data.leaves.sick_leave + "</div>";

            // Other leave credits
            leaves += "<hr class='separator'><h5>Other Leave Credits</h5>";
            if (data.leaves.other_leave && data.leaves.other_leave.trim() !== "") {
                var types = data.leaves.other_leave.split(']').map(function(item){ return item.replace('[','').trim(); }).filter(Boolean);
                var counts = data.leaves.otherleave_count.split(']').map(function(item){ return item.replace('[','').trim(); }).filter(Boolean);

                for (var i = 0; i < types.length; i++) {
                    var count = counts[i] !== undefined ? counts[i] : "0";

                    var leave = types[i];
                    var typeName = leave;
                    var duration = "";

                    if (leave.includes('(') && leave.includes(')')) {
                        typeName = leave.substring(0, leave.indexOf('(')).trim();
                        duration = leave.substring(leave.indexOf('(')+1, leave.indexOf(')')).trim();
                    }

                    leaves += "<div class='leave-box'><strong>" + typeName + ":</strong> " + count;
                    if (duration !== "") {
                        leaves += "<br><small>Duration: " + duration + "</small>";
                    }
                    leaves += "</div>";
                }
            } else {
                leaves += "<p>No other leave credits.</p>";
            }

            document.getElementById('leaveDetails').innerHTML = leaves;

            document.getElementById('employeeAccountsSection').style.display = 'none';
            document.getElementById('addEmployeeForm').style.display = 'none';
            document.getElementById('viewEmployeeForm').style.display = 'block';
        }
    };
    xhr.send();
}
let originalDetails = {};

function enableEdit() {
    // Save original values
    originalDetails.position = document.getElementById('pd_position').innerText;
    originalDetails.station = document.getElementById('pd_station').innerText;
    originalDetails.email = document.getElementById('pd_email').innerText;

    // Replace spans with inputs
    document.getElementById('pd_position').innerHTML = "<input type='text' id='input_position' value='" + originalDetails.position + "'>";
    document.getElementById('pd_station').innerHTML = "<input type='text' id='input_station' value='" + originalDetails.station + "'>";
    document.getElementById('pd_email').innerHTML = "<input type='email' id='input_email' value='" + originalDetails.email + "'>";

    // Change editActions to check and cancel
    document.getElementById('editActions').innerHTML = 
        "<button onclick='confirmSave()'>✔️</button>" +
        "<div class='divider'></div>" +
        "<button onclick='cancelEdit()'>❌</button>";
}

function cancelEdit() {
    // Restore original values
    document.getElementById('pd_position').innerText = originalDetails.position;
    document.getElementById('pd_station').innerText = originalDetails.station;
    document.getElementById('pd_email').innerText = originalDetails.email;

    // Change back to edit button
    document.getElementById('editActions').innerHTML = 
        "<button onclick='enableEdit()'>✏️</button>";
}

function confirmSave() {
    showModal("Are you sure you want to save changes?", function() {
        // Perform save via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "updateemployee.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert("Employee updated successfully.");
                cancelEdit(); // Reset to display mode
            }
        };
        var params = "employee_no=" + encodeURIComponent(document.getElementById('pd_employee_no').innerText) +
                     "&position=" + encodeURIComponent(document.getElementById('input_position').value) +
                     "&station=" + encodeURIComponent(document.getElementById('input_station').value) +
                     "&email=" + encodeURIComponent(document.getElementById('input_email').value);
        xhr.send(params);
    });
}

function confirmDelete(employeeNumber) {
    showModal("Are you sure you want to delete this employee?", function() {
        window.location.href = "deleteemployee.php?employee_number=" + employeeNumber;
    });
}

// Modal functions
let modalCallback = null;

function showModal(message, callback) {
    document.getElementById('modalMessage').innerText = message;
    document.getElementById('confirmModal').style.display = "block";
    modalCallback = callback;
}

function confirmYes() {
    document.getElementById('confirmModal').style.display = "none";
    if (modalCallback) modalCallback();
}

function confirmNo() {
    document.getElementById('confirmModal').style.display = "none";
    modalCallback = null;
}

</script>

<div id="confirmModal" class="modal">
  <div class="modal-content">
    <p id="modalMessage"></p>
    <button onclick="confirmYes()">Yes</button>
    <button onclick="confirmNo()">No</button>
  </div>

</div>
</body>
</html>

<?php
unset($_SESSION['form_data']);
unset($_SESSION['form_errors']);
$conn->close();
?>

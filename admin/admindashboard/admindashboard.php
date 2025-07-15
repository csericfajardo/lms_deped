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

// Connect to your depedlmsystem database
$servername = "localhost";
$username = "root"; // your DB username
$password = "";     // your DB password
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve HR Accounts
$sql = "SELECT hremployee_no, last_name, first_name, middle_name, email FROM hr_admin";
$result = $conn->query($sql);
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
            <button class="tab active" onclick="showTab('hr_accounts', event)">HR Accounts</button>
            <button class="tab" onclick="showTab('logs', event)">Logs</button>
        </div>

        <div id="hr_accounts" class="tab-content active">
            <div id="hrAccountsSection">
                <h3>HR Accounts
                    <button class="add-hr-btn" onclick="showAddHRForm()">Add HR Account</button>
                </h3>
               <table>
    <tr>
        <th>Employee Number</th>
        <th>Name</th>
        <th>Email</th>
        <th>Actions</th> <!-- New column for Edit and Delete -->
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $full_name = $row["last_name"] . ", " . $row["first_name"] . ", " . $row["middle_name"];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["hremployee_no"]) . "</td>";
            echo "<td>" . htmlspecialchars($full_name) . "</td>";
            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
            echo "<td>";
            echo "<button onclick=\"editHR('" . $row["hremployee_no"] . "')\">Edit</button> ";
            echo "<button onclick=\"deleteHR('" . $row["hremployee_no"] . "')\">Delete</button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No HR accounts found.</td></tr>";
    }
    ?>
</table>
            </div>
             
            <div class="edit-hr-form" id="editHRForm" style="display:none; position: relative;">
    <!-- Close button above the pane -->
    

    <div style="display: flex; align-items: center;">
        <span style="cursor: pointer; font-size: 20px; margin-right: 10px;" onclick="closeEditHRForm()">←</span>
        <h4 style="margin: 0;">Edit HR Account</h4>
    </div><br>
    <button class="close-btn" onclick="closeEditHRForm()" style="
        position: absolute;
        right: 0;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
    ">×</button>
    <br>
    
   <form action="edithr.php" method="POST">
    <input type="hidden" name="hremployee_no" id="edit_hremployee_no">
    <input type="text" name="first_name" id="edit_first_name" placeholder="First Name" required>
    <input type="text" name="middle_name" id="edit_middle_name" placeholder="Middle Name" required>
    <input type="text" name="last_name" id="edit_last_name" placeholder="Last Name" required>
    <input type="text" name="address" id="edit_address" placeholder="Address" required>
    <input type="email" name="email" id="edit_email" placeholder="Email" required>
    <button type="submit">Update</button>
</form>
</div>


            <div class="add-hr-form" id="addHRForm" style="display:none;">
                <button class="close-btn" onclick="closeAddHRForm()">×</button>
                <h4>Add HR Account</h4>
                <form action="reghr.php" method="POST" onsubmit="return submitHRForm()">
                    <input type="text" name="hremployee_no" placeholder="Employee Number" required>
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="middle_name" placeholder="Middle Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="text" name="address" placeholder="Address" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Submit</button>
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
                    <td>HR account created</td>
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

        function showAddHRForm() {
            document.getElementById('hrAccountsSection').style.display = 'none';
            document.getElementById('addHRForm').style.display = 'block';
        }

        function closeAddHRForm() {
            document.getElementById('addHRForm').style.display = 'none';
            document.getElementById('hrAccountsSection').style.display = 'block';
        }

        function submitHRForm() {
            document.getElementById('addHRForm').style.display = 'none';
            document.getElementById('hrAccountsSection').style.display = 'block';
            return true;
        }
        function deleteHR(hremployeeNumber) {
    if (confirm("Are you sure you want to delete this HR account?")) {
        window.location.href = "deletehr.php?hremployee_no=" + encodeURIComponent(hremployeeNumber);
    }
}


        function editHR(hremployeeNumber) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "gethr.php?hremployee_no=" + hremployeeNumber, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                var hr = JSON.parse(xhr.responseText);
                console.log(hr); // Debugging output

                if (hr.error) {
                    alert("Error: " + hr.error);
                } else {
                    document.getElementById('edit_hremployee_no').value = hr.hremployee_no;
                    document.getElementById('edit_first_name').value = hr.first_name;
                    document.getElementById('edit_middle_name').value = hr.middle_name;
                    document.getElementById('edit_last_name').value = hr.last_name;
                    document.getElementById('edit_address').value = hr.address;
                    document.getElementById('edit_email').value = hr.email;

                    document.getElementById('hrAccountsSection').style.display = 'none';
                    document.getElementById('editHRForm').style.display = 'block';
                }
            } else {
                alert("AJAX request failed. Status: " + xhr.status);
            }
        }
    };
    xhr.send();
}



        function closeEditHRForm() {
            document.getElementById('editHRForm').style.display = 'none';
            document.getElementById('hrAccountsSection').style.display = 'block';
        }

        function submitEditHRForm() {
            document.getElementById('editHRForm').style.display = 'none';
            document.getElementById('hrAccountsSection').style.display = 'block';
            return true;
        }


        // Prevent going back to dashboard after logout
        if (performance.navigation.type === 2) {
            location.reload(true);
        }

        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>

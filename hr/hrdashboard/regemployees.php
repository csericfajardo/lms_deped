<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: login/login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert employee account when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_no = $_POST['employee_no'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $position = $_POST['position'];
    $station = $_POST['station'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $vacation_leave = $_POST['vacation_leave'];
    $mandatory_leave = $_POST['mandatory_leave'];
    $sick_leave = $_POST['sick_leave'];

    // Check for duplicate employee_no or email
    $check_stmt = $conn->prepare("SELECT employee_no, email FROM tb_employee WHERE employee_no = ? OR email = ?");
    $check_stmt->bind_param("ss", $employee_no, $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    // Store form data in session to repopulate if error
    $_SESSION['form_data'] = $_POST;

    if ($check_stmt->num_rows > 0) {
        // Duplicate found
        $check_stmt->bind_result($existing_employee_no, $existing_email);
        $check_stmt->fetch();

        $errors = [];
        if ($existing_employee_no == $employee_no) {
            $errors['employee_no'] = "Employee number already exists.";
        }
        if ($existing_email == $email) {
            $errors['email'] = "Email already exists.";
        }

        $_SESSION['form_errors'] = $errors;

        header("Location: hrdashboard.php#addEmployeeForm");
        exit();

    } else {
        // Begin transaction for consistency
        $conn->begin_transaction();

        try {
            // Insert into tb_employee
            $stmt1 = $conn->prepare("INSERT INTO tb_employee (employee_no, first_name, middle_name, last_name, position, station, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt1->bind_param("ssssssss", $employee_no, $first_name, $middle_name, $last_name, $position, $station, $email, $password);
            $stmt1->execute();
            $stmt1->close();

            // Insert into tb_leave
            $stmt2 = $conn->prepare("INSERT INTO tb_leaves (employee_no, vacation_leave, mandatory_leave, sick_leave) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("siii", $employee_no, $vacation_leave, $mandatory_leave, $sick_leave);
            $stmt2->execute();
            $stmt2->close();

            // Commit transaction
            $conn->commit();

            // Clear form data in session after successful insert
            unset($_SESSION['form_data']);
            unset($_SESSION['form_errors']);

            header("Location: hrdashboard.php");
            exit();

        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            error_log("DB Insert Error: " . $e->getMessage());
            echo "An unexpected error occurred. Please try again later.";
        }
    }

    $check_stmt->close();
}

$conn->close();
?>

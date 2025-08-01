<?php
session_start();

$login_message = "";

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $input_password = $_POST["password"];

    // Prepare SQL to prevent SQL injection
    $stmt = $conn->prepare("SELECT hremployee_no, password FROM hr_admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hremployee_no, $db_password);
        $stmt->fetch();

        // Use password_verify to check hashed password
        if (password_verify($input_password, $db_password)) {
            $_SESSION['admin_email'] = $email;
            $_SESSION['hremployee_no'] = $hremployee_no; // Save hremployee_no to session
            header("Location: ../hrdashboard/hrdashboard.php");
            exit();
        } else {
            $login_message = "Invalid email or password.";
        }
    } else {
        $login_message = "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HR Login</title>
  <link rel="stylesheet" href="loginstyle.css" />
</head>
<body>
  <div class="login-container">
    <form action="login.php" method="POST" class="login-form" onsubmit="return validateForm()">
      <h2>HR Login</h2>
      <?php if ($login_message !== "") { echo "<p style='color:red;'>$login_message</p>"; } ?>
      <div class="input-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required />
      </div>
      <div class="input-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required />
      </div>
      <button type="submit">Login</button>
    </form>
  </div>

  <script src="loginscript.js"></script>
</body>
</html>

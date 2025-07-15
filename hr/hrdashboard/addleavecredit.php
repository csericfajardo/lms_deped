<?php
session_start();
header('Content-Type: application/json');

// ✅ Check if HR is logged in
if (!isset($_SESSION['hremployee_no'])) {
    echo json_encode(["success" => false, "message" => "Not authorized. Please login again."]);
    exit();
}

// ✅ Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// ✅ Get POST data safely
$employee_no = $_POST['employee_no'];
$leave_type = $_POST['leave_type'];
$leave_count = intval($_POST['leave_count']);
$leave_details = $_POST['leave_details'];
$leave_expiration = $_POST['leave_expiration'];
$hremployee_no = $_SESSION['hremployee_no'];

// ✅ Insert into tb_addleavecredit
$stmt = $conn->prepare("INSERT INTO tb_addleavecredit (employee_no, hremployee_no, leave_type, leave_count, leave_details, leave_expiration) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiss", $employee_no, $hremployee_no, $leave_type, $leave_count, $leave_details, $leave_expiration);

if ($stmt->execute()) {

    // ✅ Update tb_leaves
    if (in_array($leave_type, ["Vacation Leave", "Mandatory Leave", "Sick Leave"])) {
        // Update standard leave columns
        $column = "";
        if ($leave_type == "Vacation Leave") $column = "vacation_leave";
        if ($leave_type == "Mandatory Leave") $column = "mandatory_leave";
        if ($leave_type == "Sick Leave") $column = "sick_leave";

        $update = $conn->prepare("UPDATE tb_leaves SET $column = $column + ? WHERE employee_no = ?");
        $update->bind_param("is", $leave_count, $employee_no);
        if ($update->execute()) {
            echo json_encode(["success" => true, "message" => "$leave_type credit added successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed updating tb_leaves for standard leave.", "error" => $conn->error]);
        }
        $update->close();

    } else {
        // ✅ Handle other leaves (consistent regex parsing)
        $sql = "SELECT other_leave, otherleave_count FROM tb_leaves WHERE employee_no = ?";
        $select = $conn->prepare($sql);
        $select->bind_param("s", $employee_no);
        $select->execute();
        $result = $select->get_result();

        if ($row = $result->fetch_assoc()) {
            $other_leave = $row['other_leave'];
            $otherleave_count = $row['otherleave_count'];

            preg_match_all('/\[(.*?)\]/', $other_leave, $leave_matches);
            preg_match_all('/\[(.*?)\]/', $otherleave_count, $count_matches);

            $leave_types = $leave_matches[1];
            $counts = array_map('intval', $count_matches[1]);

            $found = false;
            for ($i = 0; $i < count($leave_types); $i++) {
                if ($leave_types[$i] == $leave_type) {
                    $counts[$i] += $leave_count;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Add new leave type and count
                $leave_types[] = $leave_type;
                $counts[] = $leave_count;
            }

            // ✅ Rebuild strings
            $updated_types = "";
            foreach ($leave_types as $t) {
                $updated_types .= "[" . $t . "]";
            }

            $updated_counts = "";
            foreach ($counts as $c) {
                $updated_counts .= "[" . $c . "]";
            }

            // ✅ Update tb_leaves
            $update = $conn->prepare("UPDATE tb_leaves SET other_leave = ?, otherleave_count = ? WHERE employee_no = ?");
            $update->bind_param("sss", $updated_types, $updated_counts, $employee_no);
            if ($update->execute()) {
                echo json_encode(["success" => true, "message" => "$leave_type credit added successfully to other leaves."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed updating tb_leaves for other leave.", "error" => $conn->error]);
            }
            $update->close();

        } else {
            echo json_encode(["success" => false, "message" => "Employee leave record not found."]);
        }
        $select->close();
    }

} else {
    echo json_encode(["success" => false, "message" => "Error adding leave credit.", "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>

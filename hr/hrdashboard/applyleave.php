<?php
session_start();

// Set header for JSON response
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "depedlmsystem";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_no = $_POST['employee_no'];
    $leave_type = $_POST['type_of_leave'];
    $no_of_days = intval($_POST['no_of_days']);
    $inclusive_days = $_POST['inclusive_dates'];
    $details_of_leaves = $_POST['details_of_leave'];
    $date_of_application = $_POST['date_of_application'];

    // Insert into tb_leaveapplications
    $stmt = $conn->prepare("INSERT INTO tb_leaveapplications (leave_type, employee_no, no_of_days, inclusive_days, details_of_leaves, date_of_application) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $leave_type, $employee_no, $no_of_days, $inclusive_days, $details_of_leaves, $date_of_application);

    if ($stmt->execute()) {
        // Update tb_leaves accordingly
        if ($leave_type == "Vacation Leave" || $leave_type == "Mandatory Leave" || $leave_type == "Sick Leave") {
            $column = "";
            if ($leave_type == "Vacation Leave") $column = "vacation_leave";
            if ($leave_type == "Mandatory Leave") $column = "mandatory_leave";
            if ($leave_type == "Sick Leave") $column = "sick_leave";

            $update = $conn->prepare("UPDATE tb_leaves SET $column = GREATEST($column - ?, 0) WHERE employee_no = ?");
            $update->bind_param("is", $no_of_days, $employee_no);
            $update->execute();
            $update->close();

        } else {
            // Update other leave counts
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
                $counts = $count_matches[1];

                for ($i = 0; $i < count($leave_types); $i++) {
                    if ($leave_types[$i] == $leave_type) {
                        $counts[$i] = max(0, intval($counts[$i]) - $no_of_days);
                        break;
                    }
                }

                // Rebuild counts string
                $updated_counts = "";
                foreach ($counts as $c) {
                    $updated_counts .= "[" . $c . "]";
                }

                // Update database
                $update = $conn->prepare("UPDATE tb_leaves SET otherleave_count = ? WHERE employee_no = ?");
                $update->bind_param("ss", $updated_counts, $employee_no);
                $update->execute();
                $update->close();
            }
            $select->close();
        }

        $stmt->close();

        // Return success
        echo json_encode(["success" => true, "message" => "Leave application submitted successfully."]);
        $conn->close();
        exit();

    } else {
        echo json_encode(["success" => false, "message" => "Error submitting leave application: " . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit();
    }
}

$conn->close();
echo json_encode(["success" => false, "message" => "Invalid request."]);
exit();
?>

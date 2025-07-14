function showTab(tabId, event) {
  var contents = document.getElementsByClassName("tab-content");
  for (var i = 0; i < contents.length; i++) {
    contents[i].classList.remove("active");
  }

  var tabs = document.getElementsByClassName("tab");
  for (var i = 0; i < tabs.length; i++) {
    tabs[i].classList.remove("active");
  }

  document.getElementById(tabId).classList.add("active");
  event.currentTarget.classList.add("active");
}

function showAddEmployeeForm() {
  document.getElementById("employeeAccountsSection").style.display = "none";
  document.getElementById("addEmployeeForm").style.display = "block";
}

function closeAddEmployeeForm() {
  document.getElementById("addEmployeeForm").style.display = "none";
  document.getElementById("employeeAccountsSection").style.display = "block";
}

function closeViewEmployeeForm() {
  document.getElementById("viewEmployeeForm").style.display = "none";
  document.getElementById("employeeAccountsSection").style.display = "block";
}

function deleteEmployee(employeeNumber) {
  if (confirm("Are you sure you want to delete this employee account?")) {
    window.location.href =
      "deleteemployee.php?employee_number=" + employeeNumber;
  }
}

function viewEmployee(employeeNumber) {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "viewemployee.php?employee_number=" + employeeNumber, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      var data = JSON.parse(xhr.responseText);

      if (data.error) {
        alert("Error: " + data.error);
        return;
      }

      if (!data.employee) {
        alert("Employee data not found.");
        return;
      }

      // Build personal details
      var details = `
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <h5>Personal Details</h5>
          <div id="editActions">
            <button onclick="enableEdit()">✏️</button>
          </div>
        </div>
        <p><strong>Employee No:</strong> <span id="pd_employee_no">${data.employee.employee_no}</span></p>
        <p><strong>Name:</strong> 
          <span id="pd_last_name">${data.employee.last_name}</span>, 
          <span id="pd_first_name">${data.employee.first_name}</span>, 
          <span id="pd_middle_name">${data.employee.middle_name}</span>
        </p>
        <p><strong>Position:</strong> <span id="pd_position">${data.employee.position}</span></p>
        <p><strong>Station:</strong> <span id="pd_station">${data.employee.station}</span></p>
        <p><strong>Email:</strong> <span id="pd_email">${data.employee.email}</span></p>
      `;

      document.getElementById("employeeDetails").innerHTML = details;

      // Build leave credits
      var leaves = "<h5>Leave Credits</h5>";
      leaves += createLeaveBox(
        "Vacation Leave",
        data.leaves.vacation_leave,
        data.employee.employee_no
      );
      leaves += createLeaveBox(
        "Mandatory Leave",
        data.leaves.mandatory_leave,
        data.employee.employee_no
      );
      leaves += createLeaveBox(
        "Sick Leave",
        data.leaves.sick_leave,
        data.employee.employee_no
      );

      // Other leave credits
      leaves += "<hr class='separator'><h5>Other Leave Credits</h5>";
      if (data.leaves.other_leave && data.leaves.other_leave.trim() !== "") {
        var types = data.leaves.other_leave
          .split("]")
          .map((item) => item.replace("[", "").trim())
          .filter(Boolean);
        var counts = data.leaves.otherleave_count
          .split("]")
          .map((item) => item.replace("[", "").trim())
          .filter(Boolean);

        for (var i = 0; i < types.length; i++) {
          var count = counts[i] !== undefined ? counts[i] : "0";
          var leave = types[i];
          var typeName = leave.includes("(")
            ? leave.substring(0, leave.indexOf("(")).trim()
            : leave;
          var duration = "";

          if (leave.includes("(") && leave.includes(")")) {
            duration = leave
              .substring(leave.indexOf("(") + 1, leave.indexOf(")"))
              .trim();
          }

          leaves += createLeaveBox(
            typeName,
            count,
            data.employee.employee_no,
            duration
          );
        }
      } else {
        leaves += "<p>No other leave credits.</p>";
      }

      document.getElementById("leaveDetails").innerHTML = leaves;

      // Show view form
      document.getElementById("employeeAccountsSection").style.display = "none";
      document.getElementById("addEmployeeForm").style.display = "none";
      document.getElementById("viewEmployeeForm").style.display = "block";
    }
  };
  xhr.send();
}

function createLeaveBox(type, count, employeeNo, duration = "") {
  let box = `<div class='leave-box'>
    <strong>${type}:</strong> ${count}`;
  if (duration !== "") {
    box += `<br><small>Duration: ${duration}</small>`;
  }
  box += `<button style="float:right;" onclick="openLeaveModal('${employeeNo}', '${type}')">Apply</button>`;
  box += "</div>";
  return box;
}

function openLeaveModal(employeeNo, typeOfLeave) {
  document.getElementById("modal_employee_no").value = employeeNo;
  document.getElementById("modal_type_of_leave").value = typeOfLeave;
  document.getElementById("modal_leave_type_display").innerText = typeOfLeave;
  document.getElementById("leaveModal").style.display = "flex";
}

function closeLeaveModal() {
  document.getElementById("leaveModal").style.display = "none";
}

let originalDetails = {};

function enableEdit() {
  // Save original values
  originalDetails.last_name = document.getElementById("pd_last_name").innerText;
  originalDetails.first_name =
    document.getElementById("pd_first_name").innerText;
  originalDetails.middle_name =
    document.getElementById("pd_middle_name").innerText;
  originalDetails.position = document.getElementById("pd_position").innerText;
  originalDetails.station = document.getElementById("pd_station").innerText;
  originalDetails.email = document.getElementById("pd_email").innerText;

  // Replace spans with inputs for name fields
  document.getElementById(
    "pd_last_name"
  ).innerHTML = `<input type='text' id='input_last_name' value='${originalDetails.last_name}'>`;
  document.getElementById(
    "pd_first_name"
  ).innerHTML = `<input type='text' id='input_first_name' value='${originalDetails.first_name}'>`;
  document.getElementById(
    "pd_middle_name"
  ).innerHTML = `<input type='text' id='input_middle_name' value='${originalDetails.middle_name}'>`;

  // Other editable fields
  document.getElementById(
    "pd_position"
  ).innerHTML = `<input type='text' id='input_position' value='${originalDetails.position}'>`;
  document.getElementById(
    "pd_station"
  ).innerHTML = `<input type='text' id='input_station' value='${originalDetails.station}'>`;
  document.getElementById(
    "pd_email"
  ).innerHTML = `<input type='email' id='input_email' value='${originalDetails.email}'>`;

  // Change editActions to check and cancel
  document.getElementById("editActions").innerHTML =
    "<button onclick='confirmSave()'>✔️</button>" +
    "<div class='divider'></div>" +
    "<button onclick='cancelEdit()'>❌</button>";
}

function cancelEdit() {
  document.getElementById("pd_last_name").innerText = originalDetails.last_name;
  document.getElementById("pd_first_name").innerText =
    originalDetails.first_name;
  document.getElementById("pd_middle_name").innerText =
    originalDetails.middle_name;
  document.getElementById("pd_position").innerText = originalDetails.position;
  document.getElementById("pd_station").innerText = originalDetails.station;
  document.getElementById("pd_email").innerText = originalDetails.email;

  document.getElementById("editActions").innerHTML =
    "<button onclick='enableEdit()'>✏️</button>";
}

function confirmSave() {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "updateemployee.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      alert("Employee updated successfully.");
      // Reload personal details with updated data
      var employeeNumber = document.getElementById("pd_employee_no").innerText;
      viewEmployee(employeeNumber);
    }
  };
  var params =
    "employee_no=" +
    encodeURIComponent(document.getElementById("pd_employee_no").innerText) +
    "&last_name=" +
    encodeURIComponent(document.getElementById("input_last_name").value) +
    "&first_name=" +
    encodeURIComponent(document.getElementById("input_first_name").value) +
    "&middle_name=" +
    encodeURIComponent(document.getElementById("input_middle_name").value) +
    "&position=" +
    encodeURIComponent(document.getElementById("input_position").value) +
    "&station=" +
    encodeURIComponent(document.getElementById("input_station").value) +
    "&email=" +
    encodeURIComponent(document.getElementById("input_email").value);
  xhr.send(params);
}

function submitLeaveApplication() {
  var form = document.getElementById("leaveForm");
  var formData = new FormData(form);

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "applyleave.php", true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      var response = JSON.parse(xhr.responseText);
      if (response.success) {
        alert(response.message);
        closeLeaveModal();

        // Reload leave credits without reloading full page
        var employeeNo = document.getElementById("modal_employee_no").value;
        viewEmployee(employeeNo);
      } else {
        alert("Failed: " + response.message);
      }
    }
  };
  xhr.send(formData);
}

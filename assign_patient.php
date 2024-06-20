<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Fetch patients
$patients = [];
$patient_query = "SELECT id, full_name FROM patients ORDER BY id DESC";
$patient_result = $conn->query($patient_query);
if ($patient_result->num_rows > 0) {
    while ($row = $patient_result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Fetch doctors
$doctors = [];
$doctor_query = "SELECT id, full_name FROM doctors";
$doctor_result = $conn->query($doctor_query);
if ($doctor_result->num_rows > 0) {
    while ($row = $doctor_result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Fetch current assignments
$current_assignments = [];
$sql_current_assignments = "SELECT pa.patient_id, pa.doctor_id, d.full_name AS doctor_name FROM patient_doctor_assignment pa LEFT JOIN doctors d ON pa.doctor_id = d.id";
$result_current_assignments = $conn->query($sql_current_assignments);
if ($result_current_assignments->num_rows > 0) {
    while ($row = $result_current_assignments->fetch_assoc()) {
        $current_assignments[$row['patient_id']] = $row['doctor_name'];
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['assign'])) {
        $patient_id = $_POST['patient'];
        $doctor_id = $_POST['doctor'];

        // Insert new assignment or update existing one
        $sql_check = $conn->prepare("SELECT * FROM patient_doctor_assignment WHERE patient_id = ? AND doctor_id = ?");
        $sql_check->bind_param("ii", $patient_id, $doctor_id);
        $sql_check->execute();
        $result_check = $sql_check->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = "This patient is already assigned to the selected doctor.";
        } else {
            // Insert new assignment
            $sql_insert = $conn->prepare("INSERT INTO patient_doctor_assignment (patient_id, doctor_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE doctor_id = VALUES(doctor_id)");
            $sql_insert->bind_param("ii", $patient_id, $doctor_id);

            if ($sql_insert->execute() === TRUE) {
                $success_message = "Patient successfully assigned to doctor.";
            } else {
                $error_message = "Error: " . $sql_insert->error;
            }
        }
        $sql_check->close();
        if (isset($sql_insert)) {
            $sql_insert->close();
        }
    } elseif (isset($_POST['reassign'])) {
        $patient_id = $_POST['patient_id'];
        $new_doctor_id = $_POST['new_doctor'];

        // Update current assignment
        $sql_update = $conn->prepare("UPDATE patient_doctor_assignment SET doctor_id = ? WHERE patient_id = ?");
        $sql_update->bind_param("ii", $new_doctor_id, $patient_id);

        if ($sql_update->execute() === TRUE) {
            $success_message = "Patient successfully reassigned to new doctor.";
        } else {
            $error_message = "Error: " . $sql_update->error;
        }

        if (isset($sql_update)) {
            $sql_update->close();
        }
    }
}

// Fetch assigned patients and their doctors
$assignments = [];
$sql_assignments = "SELECT p.id AS patient_id, p.full_name AS patient_name, d.id AS doctor_id, d.full_name AS doctor_name
                    FROM patients p
                    LEFT JOIN patient_doctor_assignment pa ON p.id = pa.patient_id
                    LEFT JOIN doctors d ON pa.doctor_id = d.id";
$result_assignments = $conn->query($sql_assignments);

if ($result_assignments->num_rows > 0) {
    while ($row = $result_assignments->fetch_assoc()) {
        $assignments[] = $row;
    }
}

$conn->close(); // Close the database connection at the end of all operations
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Patient to Doctor</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
    <link rel="stylesheet" href="./css/view_prescriptions.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="assign_all">
                <div class="title">
                    <h2>Assign Patient to Doctor</h2>
                </div>
                <?php if (!empty($success_message)) : ?>
                    <div class="success-message">
                        <p><?php echo $success_message; ?></p>
                        <span class="close-success"><i class="fa-solid fa-times"></i></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <p><?php echo $error_message; ?></p>
                        <span class="close-error"><i class="fa-solid fa-times"></i></span>
                    </div>
                <?php endif; ?>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="forms">
                        <label for="patient">Select Patient:</label>
                        <select name="patient" id="patient" required>
                            <option value="">--Select Patient--</option>
                            <?php foreach ($patients as $patient) : ?>
                                <?php
                                $assigned = isset($current_assignments[$patient['id']]) ? " (Assigned to " . $current_assignments[$patient['id']] . ")" : "";
                                $disabled = isset($current_assignments[$patient['id']]) ? "disabled" : "";
                                ?>
                                <option value="<?php echo $patient['id']; ?>" <?php echo $disabled; ?>><?php echo $patient['full_name'] . $assigned; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="forms">
                        <label for="doctor">Select Doctor:</label>
                        <select name="doctor" id="doctor" required>
                            <option value="">--Select Doctor--</option>
                            <?php foreach ($doctors as $doctor) : ?>
                                <option value="<?php echo $doctor['id']; ?>"><?php echo $doctor['full_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="forms">
                        <button type="submit" name="assign">Assign</button>
                    </div>
                </form>
            </div>
            <div class="assigned_all">
                <div class="title">
                    <h2>Assigned Patients and Doctors</h2>
                </div>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search patient...">
                </div>
                <div class="assigned_table">
                    <?php if (!empty($assignments)) : ?>
                        <table id="assignedTable">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Patient Name</th>
                                    <th>Doctor ID</th>
                                    <th>Doctor Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['patient_id']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['doctor_id']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['doctor_name']); ?></td>
                                        <td>
                                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                                <input type="hidden" name="patient_id" value="<?php echo $assignment['patient_id']; ?>">
                                                <div class="forms">
                                                    <label for="new_doctor">Reassign to Doctor:</label>
                                                    <select name="new_doctor" id="new_doctor" required>
                                                        <option value="">--Select Doctor--</option>
                                                        <?php foreach ($doctors as $doctor) : ?>
                                                            <option value="<?php echo $doctor['id']; ?>"><?php echo $doctor['full_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <button type="submit" name="reassign">Reassign</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>No patients assigned to doctors.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        // JavaScript to handle closing of messages
        document.querySelectorAll('.close-error, .close-success').forEach(element => {
            element.addEventListener('click', () => {
                element.parentElement.style.display = 'none';
            });
        });

        // JavaScript for search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            let filter = this.value.toUpperCase();
            let table = document.getElementById('assignedTable');
            let rows = table.getElementsByTagName('tr');

            // Loop through all table rows, and hide those who don't match the search query
            for (let i = 0; i < rows.length; i++) {
                let patientNameCell = rows[i].getElementsByTagName('td')[1];
                if (patientNameCell) {
                    let textValue = patientNameCell.textContent || patientNameCell.innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        });
    </script>
</body>

</html>

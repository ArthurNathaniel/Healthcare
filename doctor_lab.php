<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission for assigning tests to patients
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_test'])) {
    $doctor_id = $_SESSION['user_id'];
    $patient_id = $_POST['patient_id'];
    $test_name = $_POST['test_name'];
    $description = $_POST['description'];

    // Check if the patient has an appointment with this doctor
    $sql_check_appointment = $conn->prepare("SELECT a.id FROM appointments a 
                                             JOIN doctor_availability da ON a.doctor_availability_id = da.id
                                             WHERE da.doctor_id = ? AND a.patient_id = ?");
    $sql_check_appointment->bind_param("ii", $doctor_id, $patient_id);
    $sql_check_appointment->execute();
    $result_check_appointment = $sql_check_appointment->get_result();

    if ($result_check_appointment->num_rows > 0) {
        // Insert into database
        $sql_insert = $conn->prepare("INSERT INTO lab_prescriptions (doctor_id, patient_id, test_name, description) VALUES (?, ?, ?, ?)");
        $sql_insert->bind_param("iiss", $doctor_id, $patient_id, $test_name, $description);

        if ($sql_insert->execute() === TRUE) {
            $success_message = "Test successfully assigned to the patient.";
        } else {
            $error_message = "Error: " . $sql_insert->error;
        }

        $sql_insert->close();
    } else {
        $error_message = "Patient does not have an appointment with you. Cannot assign test.";
    }

    $sql_check_appointment->close();
}

// Fetch prescribed tests
$prescriptions = [];
$sql_prescriptions = "SELECT lp.id, lp.test_name, lp.description, lp.prescribed_date, 
                             p.full_name AS patient_name, d.full_name AS doctor_name 
                      FROM lab_prescriptions lp
                      JOIN patients p ON lp.patient_id = p.id
                      JOIN doctors d ON lp.doctor_id = d.id
                      ORDER BY lp.prescribed_date DESC";
$result_prescriptions = $conn->query($sql_prescriptions);
if ($result_prescriptions->num_rows > 0) {
    while ($row = $result_prescriptions->fetch_assoc()) {
        $prescriptions[] = $row;
    }
}

// Fetch uploaded reports with indication if report is uploaded or not
$reports = [];
$sql_reports = "SELECT lr.id, lr.report_name, lr.report_file, lr.patient_id, p.full_name AS patient_name, 
                       CASE WHEN lr.report_file IS NULL THEN 'not_uploaded' ELSE 'uploaded' END AS report_status
                FROM lab_reports lr
                JOIN patients p ON lr.patient_id = p.id
                ORDER BY lr.id DESC";
$result_reports = $conn->query($sql_reports);
if ($result_reports->num_rows > 0) {
    while ($row = $result_reports->fetch_assoc()) {
        $reports[] = $row;
    }
}

// Fetch patients who have appointments with this doctor
$patients = [];
$sql_patients = $conn->prepare("SELECT p.id, p.full_name 
                                FROM patients p
                                JOIN appointments a ON p.id = a.patient_id
                                JOIN doctor_availability da ON a.doctor_availability_id = da.id
                                WHERE da.doctor_id = ?");
$sql_patients->bind_param("i", $_SESSION['user_id']);
$sql_patients->execute();
$result_patients = $sql_patients->get_result();
if ($result_patients->num_rows > 0) {
    while ($row = $result_patients->fetch_assoc()) {
        $patients[] = $row;
    }
}
$sql_patients->close();

$conn->close(); // Close the database connection at the end of all operations
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Laboratory Test to Patient</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/lab.css">
</head>
<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="assign_all">
                <div class="title">
                    <h2>Assign Laboratory Test to Patient</h2>
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
                        <label for="patient_id">Select Patient:</label>
                        <select name="patient_id" id="patient_id" required>
                            <option value="">--Select Patient--</option>
                            <?php foreach ($patients as $patient) : ?>
                                <option value="<?php echo $patient['id']; ?>"><?php echo $patient['full_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="forms">
                        <label for="test_name">Test Name:</label>
                        <input type="text" name="test_name" id="test_name" required>
                    </div>
                    <div class="forms">
                        <label for="description">Description:</label>
                        <textarea name="description" id="description" required></textarea>
                    </div>
                    <div class="forms">
                        <button type="submit" name="assign_test">Assign Test</button>
                    </div>
                </form>

               <div class="prescribed_test">
               <h3>Prescribed Tests</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Doctor Name</th>
                            <th>Test Name</th>
                            <th>Description</th>
                            <th>Prescribed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $prescription) : ?>
                            <tr>
                                <td><?php echo $prescription['patient_name']; ?></td>
                                <td><?php echo $prescription['doctor_name']; ?></td>
                                <td><?php echo $prescription['test_name']; ?></td>
                                <td><?php echo $prescription['description']; ?></td>
                                <td><?php echo $prescription['prescribed_date']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
               </div>

                <div class="uploaded_reports">
                <h3>Uploaded Reports</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Report Name</th>
                            <th>Report Status</th>
                            <th>Report File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report) : ?>
                            <tr>
                                <td><?php echo $report['patient_name']; ?></td>
                                <td><?php echo $report['report_name']; ?></td>
                                <td>
                                    <?php if ($report['report_status'] == 'uploaded') : ?>
                                        <i class="fa-solid fa-check-circle" style="color: green;"></i>
                                    <?php else : ?>
                                        <i class="fa-solid fa-circle" style="color: red;"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($report['report_file']) : ?>
                                        <a href="<?php echo $report['report_file']; ?>" target="_blank">View Report</a>
                                    <?php else : ?>
                                        Not Uploaded
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
    </script>
</body>
</html>

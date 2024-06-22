<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Fetch lab prescriptions and uploaded reports for the logged-in patient
$patient_id = $_SESSION['user_id'];
$prescriptions = [];
$sql_prescriptions = $conn->prepare("SELECT lp.id AS prescription_id, lp.test_name, lp.description, lp.prescribed_date, 
                                     d.full_name AS doctor_name, lr.id AS report_id, lr.report_name, lr.report_file
                                     FROM lab_prescriptions lp
                                     JOIN doctors d ON lp.doctor_id = d.id
                                     LEFT JOIN lab_reports lr ON lp.id = lr.prescription_id AND lr.patient_id = ?
                                     WHERE lp.patient_id = ?
                                     ORDER BY lp.prescribed_date DESC");
$sql_prescriptions->bind_param("ii", $patient_id, $patient_id);
$sql_prescriptions->execute();
$result_prescriptions = $sql_prescriptions->get_result();
if ($result_prescriptions->num_rows > 0) {
    while ($row = $result_prescriptions->fetch_assoc()) {
        $prescriptions[] = $row;
    }
}
$sql_prescriptions->close();

// Handle form submission for uploading report or deleting existing report
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['upload_report'])) {
        $report_name = $_POST['report_name'];
        $prescription_id = $_POST['prescription_id'];
        $report_file = $_FILES['report_file'];

        // File upload logic
        $target_dir = "lab_reports/";
        $target_file = $target_dir . basename($report_file["name"]);
        $upload_ok = 1;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is a valid type
        if ($file_type != "pdf" && $file_type != "jpg" && $file_type != "png" && $file_type != "jpeg") {
            $error_message = "Only PDF, JPG, JPEG, & PNG files are allowed.";
            $upload_ok = 0;
        }

        // Check if upload is okay
        if ($upload_ok && move_uploaded_file($report_file["tmp_name"], $target_file)) {
            // Insert new report into database
            $sql_insert = $conn->prepare("INSERT INTO lab_reports (patient_id, prescription_id, report_name, report_file) VALUES (?, ?, ?, ?)");
            $sql_insert->bind_param("iiss", $patient_id, $prescription_id, $report_name, $target_file);

            if ($sql_insert->execute()) {
                $success_message = "Report successfully uploaded.";
                // Redirect to avoid form resubmission on refresh
                header("Location: {$_SERVER['PHP_SELF']}");
                exit();
            } else {
                $error_message = "Error: " . $sql_insert->error;
            }

            $sql_insert->close();
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    } elseif (isset($_POST['delete_report'])) {
        $report_id = $_POST['report_id'];
        $sql_delete = $conn->prepare("DELETE FROM lab_reports WHERE id = ?");
        $sql_delete->bind_param("i", $report_id);

        if ($sql_delete->execute()) {
            $success_message = "Report successfully deleted.";
            // Redirect to avoid form resubmission on refresh
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        } else {
            $error_message = "Error: " . $sql_delete->error;
        }

        $sql_delete->close();
    }
}

$conn->close(); // Close the database connection at the end of all operations
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Laboratory Report and Prescribed Lab Tests</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
    <style>
        .report-status-icon {
            font-size: 1.2em;
            vertical-align: middle;
            margin-left: 5px;
        }
    </style>
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="assign_all">
                <div class="title">
                    <h2>Upload Laboratory Report and View Prescribed Lab Tests</h2>
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

                <h3>Prescribed Lab Tests</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Test Name</th>
                            <th>Description</th>
                            <th>Prescribed Date</th>
                            <th>Report</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($prescriptions)) : ?>
                            <?php foreach ($prescriptions as $prescription) : ?>
                                <tr>
                                    <td><?php echo $prescription['doctor_name']; ?></td>
                                    <td><?php echo $prescription['test_name']; ?></td>
                                    <td><?php echo $prescription['description']; ?></td>
                                    <td><?php echo $prescription['prescribed_date']; ?></td>
                                    <td>
                                        <?php if ($prescription['report_id']) : ?>
                                            <a href="<?php echo $prescription['report_file']; ?>" target="_blank"><?php echo $prescription['report_name']; ?></a>
                                        <?php else : ?>
                                            Not Uploaded
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($prescription['report_id']) : ?>
                                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                                <input type="hidden" name="report_id" value="<?php echo $prescription['report_id']; ?>">
                                                <button type="submit" name="delete_report">Delete</button>
                                            </form>
                                        <?php else : ?>
                                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                                                <input type="hidden" name="prescription_id" value="<?php echo $prescription['prescription_id']; ?>">
                                                <div class="forms">
                                                    <input type="text" name="report_name" placeholder="Report Name" value="<?php echo $prescription['test_name']; ?>" readonly>

                                                </div> <input type="file" name="report_file" required>
                                                <button type="submit" name="upload_report">Upload</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">No lab tests prescribed yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
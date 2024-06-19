<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a patient
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'] ?? null;
$error_message = '';

if ($patient_id) {
    // Fetch prescriptions for the logged-in patient
    $sql = "SELECT p.id, p.prescription, p.created_at, d.full_name AS doctor_name 
            FROM prescriptions p
            JOIN doctors d ON p.doctor_id = d.id
            WHERE p.patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $prescriptions = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $prescriptions = [];
    }

    $stmt->close();
} else {
    $error_message = "Patient ID is missing.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/view_prescriptions.css">
   
</head>
<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="prescriptions_list">
                <div class="title">
                    <h2>My Prescriptions</h2>
                </div>
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($prescriptions)) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Doctor Name</th>
                                <th>Prescription</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $prescription) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prescription['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['prescription']); ?></td>
                                    <td><?php echo date('m/d/Y h:i A', strtotime($prescription['created_at'])); ?></td>
                                    <td>
                                        <button class="print-prescription-button" onclick="printPrescription(<?php echo $prescription['id']; ?>)">Print</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No prescriptions found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function printPrescription(prescriptionId) {
            var url = 'print_prescription.php?id=' + prescriptionId;
            var newWindow = window.open(url, '_blank');
            newWindow.focus();
        }
    </script>
</body>
</html>

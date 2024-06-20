<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a doctor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Fetch list of patients
$sql = "SELECT id, full_name FROM patients";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $patients = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $patients = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient List</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/patients_list.css">
</head>
<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?> 
        <div class="context">
            <div class="patients_list">
                <div class="title">
                    <h2>Patients List</h2>
                </div>
                <?php if (!empty($patients)) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($patient['id']); ?></td>
                                    <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                    <td class="action">
                                        <button onclick="openPrescriptionForm(<?php echo $patient['id']; ?>)">Write Prescription</button>
                                        <a href="view_patient_prescriptions.php?patient_id=<?php echo $patient['id']; ?>">
                                            <button>View Prescriptions</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No patients found.</p>
                <?php endif; ?>

                <!-- Prescription Form Modal -->
                <div id="prescriptionFormModal" class="modal">
                    <div class="modal-content">
                        <span class="close-button" onclick="closePrescriptionForm()">&times;</span>
                        <form action="save_prescription.php" method="post">
                            <input type="hidden" id="patient_id" name="patient_id">
                            <div class="form-group">
                                <label for="prescription">Prescription:</label>
                                <textarea id="prescription" name="prescription" required></textarea>
                            </div>
                            <button type="submit">Save Prescription</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPrescriptionForm(patientId) {
            document.getElementById('patient_id').value = patientId;
            document.getElementById('prescriptionFormModal').style.display = 'block';
        }

        function closePrescriptionForm() {
            document.getElementById('prescriptionFormModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('prescriptionFormModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

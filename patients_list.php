<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a doctor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    echo "Error: User ID not found in session.";
    exit();
}

$doctor_id = $_SESSION['user_id']; // Get user_id from session

// Fetch list of patients who have booked an appointment with the logged-in doctor along with appointment status
$sql = "SELECT DISTINCT p.id, p.full_name, a.booking_date, a.booking_time, da.end_time
        FROM patients p
        JOIN appointments a ON p.id = a.patient_id
        JOIN doctor_availability da ON a.doctor_availability_id = da.id
        WHERE da.doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $patients = $result->fetch_all(MYSQLI_ASSOC);

    // Determine the current date and time
    date_default_timezone_set('UTC'); // Set the timezone to UTC
    $current_datetime = new DateTime();

    // Iterate through patients to determine appointment status
    foreach ($patients as &$patient) {
        // Combine booking_date and booking_time into a DateTime object
        $appointment_datetime = new DateTime($patient['booking_date'] . ' ' . $patient['booking_time']);
        
        // Combine booking_date and end_time into a DateTime object
        $end_datetime = new DateTime($patient['booking_date'] . ' ' . $patient['end_time']);
        
        // Determine status based on current datetime and end datetime
        if ($current_datetime > $end_datetime) {
            $patient['status'] = 'Ended';
            $patient['status_class'] = 'ended';
        } else {
            $patient['status'] = 'Active';
            $patient['status_class'] = 'active';
        }
    }
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
                <input type="text" id="searchInput" onkeyup="searchPatients()" placeholder="Search for patients...">
                <?php if (!empty($patients)) : ?>
                    <table id="patientsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Appointment Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient) : ?>
                                <tr class="<?php echo $patient['status_class']; ?>">
                                    <td><?php echo htmlspecialchars($patient['id']); ?></td>
                                    <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($patient['booking_date'] . ' ' . $patient['booking_time']); ?></td>
                                    <td><?php echo $patient['status']; ?></td>
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
        function searchPatients() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("patientsTable");
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                td = tr[i].getElementsByTagName("td")[1]; // Full Name column
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }

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
    <style>
        .ended {
            background-color: #f8d7da;
        }
        .active {
            background-color: #d4edda;
        }
    </style>
</body>
</html>

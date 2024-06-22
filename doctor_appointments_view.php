<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$appointments = [];

// Fetch patients who booked the doctor's slots
$sql_appointments = $conn->prepare("
    SELECT a.id AS appointment_id, p.full_name AS patient_name, da.date, da.start_time, da.end_time, a.booking_date, a.booking_time
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctor_availability da ON a.doctor_availability_id = da.id
    WHERE da.doctor_id = ?
    ORDER BY da.date, da.start_time
");
$sql_appointments->bind_param("i", $doctor_id);
$sql_appointments->execute();
$result_appointments = $sql_appointments->get_result();
if ($result_appointments->num_rows > 0) {
    while ($row = $result_appointments->fetch_assoc()) {
        $appointments[] = $row;
    }
}

$sql_appointments->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="appointments_all">
                <div class="title">
                    <h2>Your Appointments</h2>
                </div>
                <?php if (!empty($appointments)) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Appointment ID</th>
                                <th>Patient Name</th>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Booking Date</th>
                                <th>Booking Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['date']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['booking_date']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['booking_time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No appointments booked.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$booked_slots = [];

// Fetch booked slots for the patient
$sql_booked_slots = $conn->prepare("
    SELECT a.id AS appointment_id, d.full_name AS doctor_name, da.date, da.start_time, da.end_time, a.booking_date, a.booking_time
    FROM appointments a
    JOIN doctor_availability da ON a.doctor_availability_id = da.id
    JOIN doctors d ON da.doctor_id = d.id
    WHERE a.patient_id = ?
    ORDER BY da.date, da.start_time
");
$sql_booked_slots->bind_param("i", $patient_id);
$sql_booked_slots->execute();
$result_booked_slots = $sql_booked_slots->get_result();
if ($result_booked_slots->num_rows > 0) {
    while ($row = $result_booked_slots->fetch_assoc()) {
        $booked_slots[] = $row;
    }
}

$sql_booked_slots->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booked Slots</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="booked_slots_all">
                <div class="title">
                    <h2>My Booked Slots</h2>
<br>
                  
                </div>
                <?php if (!empty($booked_slots)) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Appointment ID</th>
                                <th>Doctor Name</th>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Booking Date</th>
                                <th>Booking Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($booked_slots as $slot) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($slot['appointment_id']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['date']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['booking_date']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['booking_time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No booked slots.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

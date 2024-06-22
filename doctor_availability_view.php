<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$availability = [];

// Fetch doctor's availability
$sql_availability = $conn->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ? ORDER BY date, start_time");
$sql_availability->bind_param("i", $doctor_id);
$sql_availability->execute();
$result_availability = $sql_availability->get_result();
if ($result_availability->num_rows > 0) {
    while ($row = $result_availability->fetch_assoc()) {
        $availability[] = $row;
    }
}

$sql_availability->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Availability</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="availability_all">
                <div class="title">
                    <h2>Your Availability</h2>
                </div>
                <?php if (!empty($availability)) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Phone Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availability as $slot) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($slot['date']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['phone_number']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No availability set.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

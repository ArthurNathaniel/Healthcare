<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a patient
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id']; // Assuming 'user_id' stores patient's ID in session

// Query to fetch assigned doctors for the patient
$sql = "SELECT d.id, d.full_name
        FROM doctors d
        INNER JOIN patient_doctor_assignment pda ON d.id = pda.doctor_id
        WHERE pda.patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$assigned_doctors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $assigned_doctors[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Doctors</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/assigned_doctors.css">
</head>
<body>
    <div class="page_all">
        <?php include 'sidebar.php' ?>
        <div class="context">
            <div class="assigned_doctors">
                <div class="title">
                    <h2>Your Assigned Doctors</h2>
                </div>
                <div class="doctor_list">
                    <?php if (!empty($assigned_doctors)) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Doctor ID</th>
                                    <th>Doctor Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assigned_doctors as $doctor) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doctor['id']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>No doctors assigned.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

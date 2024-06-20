<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a patient
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Fetch prescriptions for the current patient
$patient_id = $_SESSION['user_id']; // Assuming 'user_id' stores patient's ID in session

// Query with JOIN to get doctor's name
$sql = "SELECT p.id, d.full_name AS doctor_name, p.prescription, p.created_at 
        FROM prescriptions p 
        LEFT JOIN doctors d ON p.doctor_id = d.id
        WHERE p.patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$prescriptions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $prescriptions[] = $row;
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
    <title>View Prescriptions</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/view_prescriptions.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php' ?>
        <div class="context">
            <div class="prescription_all">
                <div class="title">
                    <h2>Your Prescriptions</h2>
                </div>
                <div class="prescription_list">
                    <?php if (!empty($prescriptions)) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Prescription ID</th>
                                    <th>Prescription</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Action</th> <!-- New column for Action -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescriptions as $prescription) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prescription['id']); ?></td>
                                        <td><?php echo htmlspecialchars($prescription['prescription']); ?></td>
                                        <td><?php echo isset($prescription['doctor_name']) ? htmlspecialchars($prescription['doctor_name']) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($prescription['created_at']))); ?></td>
                                        <td><a href="view_prescription.php?id=<?php echo htmlspecialchars($prescription['id']); ?>">View Details</a></td> <!-- Link to view_prescription.php -->
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
    </div>
</body>

</html>

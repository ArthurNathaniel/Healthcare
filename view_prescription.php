<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a patient
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Prescription ID is required.";
    header("Location: view_prescriptions.php");
    exit();
}

$prescription_id = $_GET['id'];
$patient_id = $_SESSION['user_id']; // Assuming 'user_id' stores patient's ID in session

// Query to fetch prescription and patient details including age calculation
$sql = "SELECT p.id, p.prescription, p.created_at, d.full_name AS doctor_name, pt.gender,
               TIMESTAMPDIFF(YEAR, pt.dob, CURDATE()) AS age
        FROM prescriptions p
        LEFT JOIN doctors d ON p.doctor_id = d.id
        LEFT JOIN patients pt ON p.patient_id = pt.id
        WHERE p.id = ? AND p.patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $prescription_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $prescription = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "Prescription not found or you don't have access to view this prescription.";
    header("Location: view_prescriptions.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Prescription</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/view_prescription.css">
    <link rel="stylesheet" href="css/print_prescription.css">
    <style>
        .print-button {
            text-align: center;
            margin-top: 20px;
        }

        .print-button button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .print-button button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div class="navbar_all">
        <div class="ones">
            <div class="logo">

            </div>
            <p><i>Your Virtual Health Companion</i></p>
        </div>
        <div class="twos">
            <h1>Prescription</h1>
        </div>

    </div>
    <div class="details">
        <div class="dwtails">
            <p><strong>Prescription ID:</strong> <?php echo htmlspecialchars($prescription['id']); ?></p>
            <p><strong>Patient's Name:</strong> <span><?php echo $_SESSION['user']; ?></span></p>
            <p><strong>Patient's Gender:</strong> <span><?php echo htmlspecialchars($prescription['gender']); ?></span></p>
            <p><strong>Patient's Age:</strong> <span><?php echo htmlspecialchars($prescription['age']); ?> years</span></p>
            <p><strong>Date:</strong> <span><?php echo date('m/d/Y h:i A', strtotime($prescription['created_at'])); ?></span></p>
        </div>

    </div>
    <div class="drugs">
        <i class="fas fa-prescription"></i>
       
        <?php if (isset($prescription)) : ?>

<p><?php echo htmlspecialchars($prescription['prescription']); ?></p>
<?php else : ?>
<p>Prescription details not found.</p>
<?php endif; ?>

    </div>

    <div class="doctor_name">

        <p>Doctor's Name: <?php echo htmlspecialchars($prescription['doctor_name']); ?></p>
    </div>
   
    


   
    <div class="print-button">
        <button onclick="window.print()">Print Prescription</button>
    </div>
</body>

</html>

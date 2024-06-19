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
    // Fetch prescription and patient details based on prescription ID passed in URL
    $prescription_id = $_GET['id'] ?? null;
    if ($prescription_id) {
        $sql = "SELECT p.id, p.prescription, p.created_at, d.full_name AS doctor_name, 
                       DATEDIFF(CURDATE(), pat.dob) DIV 365 AS age,
                       pat.gender
                FROM prescriptions p
                JOIN doctors d ON p.doctor_id = d.id
                JOIN patients pat ON p.patient_id = pat.id
                WHERE p.id = ? AND p.patient_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $prescription_id, $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $prescription = $result->fetch_assoc();
        } else {
            $error_message = "Prescription not found.";
        }

        $stmt->close();
    } else {
        $error_message = "Prescription ID is missing.";
    }
} else {
    $error_message = "Patient ID is missing.";
}

// If there's an error, redirect back to the main page with an error message
if (!empty($error_message)) {
    $_SESSION['error_message'] = $error_message;
    header("Location: view_prescriptions.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Prescription</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/print_prescription.css">
</head>

<body>
    <div class="navbar_all">
        <div class="ones">
            <div class="logo">

            </div>
            <p>Your Virtual Health Companion</p>
            <!-- 
            <p><span><i class="fa-solid fa-phone"></i></span> +233 541 987 478</p>
            <p><span><i class="fa-solid fa-envelope"></i></span> info@telehaven.com</p>
            <p><span><i class="fa-solid fa-globe"></i></span> www.telehaven.com</p> -->
        </div>
        <div class="twos">
            <h1>Prescription</h1>
        </div>
    
    </div>
    <div class="details">
        <div class="dwtails">
            <p>Patient's Name: <span><?php echo $_SESSION['user']; ?></span></p>
            <p class="lasr">Date: <span><?php echo date('m/d/Y h:i A', strtotime($prescription['created_at'])); ?></span></p>
        </div>
        <div class="dwtails">
            <p>Patient's Gender: <span><?php echo htmlspecialchars($prescription['gender']); ?></span></p>
            <p class="lasr">Patient's Age: <span><?php echo htmlspecialchars($prescription['age']); ?> years</span> </p>
        </div>
    </div>
  <div class="drugs">
  <i class="fas fa-prescription"></i>
  <p><?php echo nl2br(htmlspecialchars($prescription['prescription'])); ?></p>
  </div>

    <div class="doctor_name">
        
        <p>Doctor's Name: <?php echo htmlspecialchars($prescription['doctor_name']); ?></p>
    </div>


    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
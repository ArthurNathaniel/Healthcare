<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a doctor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$prescription_id = $_GET['id'] ?? null;
$patient_id = $_GET['patient_id'] ?? null;

if ($prescription_id) {
    // Delete prescription
    $sql = "DELETE FROM prescriptions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prescription_id);
    if ($stmt->execute()) {
        header("Location: view_patient_prescriptions.php?patient_id=$patient_id");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    die("Prescription ID is missing.");
}
?>

<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a doctor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $prescription = $_POST['prescription'];

    // Save the prescription to the database
    $sql = "INSERT INTO prescriptions (doctor_id, patient_id, prescription) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $_SESSION['doctor_id'], $patient_id, $prescription);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Prescription saved successfully.";
    } else {
        $_SESSION['error_message'] = "Error saving prescription: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: patients_list.php");
    exit();
}
?>

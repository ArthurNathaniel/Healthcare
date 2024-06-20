<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a doctor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Check if user_id is set in session and it is the doctor's ID
if (!isset($_SESSION['user_id'])) {
    echo "Error: Doctor ID not found in session.";
    exit();
}

$doctor_id = $_SESSION['user_id']; // Get the doctor_id from the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $prescription = $_POST['prescription'];

    // Save the prescription to the database
    $sql = "INSERT INTO prescriptions (doctor_id, patient_id, prescription) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $doctor_id, $patient_id, $prescription);

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

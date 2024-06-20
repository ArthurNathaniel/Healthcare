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
    // Fetch prescription details
    $sql = "SELECT prescription FROM prescriptions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prescription_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $prescription = $result->fetch_assoc();
    } else {
        die("Prescription not found.");
    }

    $stmt->close();

    if (isset($_POST['update'])) {
        $new_prescription = $_POST['prescription'];

        // Update prescription
        $sql = "UPDATE prescriptions SET prescription = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_prescription, $prescription_id);
        if ($stmt->execute()) {
            header("Location: view_patient_prescriptions.php?patient_id=$patient_id");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $stmt->close();
    }
} else {
    die("Prescription ID is missing.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Prescription</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/edit_prescription.css">
</head>
<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="edit_prescription_form">
                <div class="title">
                    <h2>Edit Prescription</h2>
                </div>
                <form action="edit_prescription.php?id=<?php echo $prescription_id; ?>&patient_id=<?php echo $patient_id; ?>" method="post">
                    <div class="form_group">
                        <label for="prescription">Prescription:</label>
                        <textarea id="prescription" name="prescription" required><?php echo htmlspecialchars($prescription['prescription']); ?></textarea>
                    </div>
                    <div class="form_group">
                        <button type="submit" name="update">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

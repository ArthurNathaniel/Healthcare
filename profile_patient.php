<?php
include 'db.php';
session_start();

// Redirect to login if not authenticated or not a patient
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Fetch prescriptions for the current patient
$patient_id = $_SESSION['user_id']; // Assuming 'user_id' stores patient's ID in session


// $patient_id = $_SESSION['patient_id'];
$sql = "SELECT * FROM patients WHERE id='$patient_id'";
$result = $conn->query($sql);
$patient = $result->fetch_assoc();
$profile_image = !empty($patient['profile_image']) ? "profile_images/" . $patient['profile_image'] : "profile_images/default.png";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/profile.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php' ?>
        <div class="context">
            <div class="profile_all">
                <div class="title">
                    <h2>Patient Profile</h2>
                </div>
                <div class="profile-info">
                    <div class="profile-item">
                        <img src="<?php echo $profile_image; ?>" alt="Profile Image" class="profile-image">
                    </div>
                    <div class="profile_flex">
                    <div class="profile-item">
                        <label>Full Name:</label>
                        <p><?php echo htmlspecialchars($patient['full_name']); ?></p>
                    </div>
                    <div class="profile-item">
                        <label>Date of Birth:</label>
                        <p><?php echo htmlspecialchars($patient['dob']); ?></p>
                    </div>
                    <div class="profile-item">
                        <label>Gender:</label>
                        <p><?php echo htmlspecialchars($patient['gender']); ?></p>
                    </div>
                    <div class="profile-item">
                        <label>Email Address:</label>
                        <p><?php echo htmlspecialchars($patient['email']); ?></p>
                    </div>
                    <div class="profile-item">
                        <label>Phone Number:</label>
                        <p><?php echo htmlspecialchars($patient['phone']); ?></p>
                    </div>
                </div>
                </div>
                <div class="forms">
                    <a href="edit_profile.php" class="button">Edit Profile <i class="fa-regular fa-pen-to-square"></i></a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
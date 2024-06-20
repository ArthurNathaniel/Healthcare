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

$error_message = '';

if (isset($_POST['update'])) {
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $profile_image = $_FILES['profile_image']['name'];
    $profile_image_tmp = $_FILES['profile_image']['tmp_name'];

    if ($profile_image) {
        $profile_image_new_name = uniqid() . '-' . $profile_image;
        move_uploaded_file($profile_image_tmp, "profile_images/" . $profile_image_new_name);
        $sql = "UPDATE patients SET profile_image='$profile_image_new_name' WHERE id='$patient_id'";
        $conn->query($sql);
    }

    $sql = "UPDATE patients SET full_name='$full_name', dob='$dob', gender='$gender', email='$email', phone='$phone' WHERE id='$patient_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: profile_patient.php");
        exit();
    } else {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql = "SELECT * FROM patients WHERE id='$patient_id'";
$result = $conn->query($sql);
$patient = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="header">
        <a href="profile_patient.php">
            <span><i class="fa-solid fa-arrow-left"></i> Back</span>
        </a>
        <div class="logo"></div>
    </div>
    <div class="register_all">
        <div class="title">
            <h2>Edit Profile</h2>
        </div>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <p><?php echo $error_message; ?></p>
                <span class="close-error"><i class="fa-solid fa-times"></i></span>
            </div>
        <?php endif; ?>
        <form action="edit_profile.php" method="post" enctype="multipart/form-data">
            <div class="forms">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo $patient['full_name']; ?>" required>
            </div>

            <div class="forms_flex">
                <div class="forms">
                    <label for="dob">Date of Birth:</label>
                    <input type="text" id="dob" name="dob" value="<?php echo $patient['dob']; ?>" required>
                    <span><i class="fa-regular fa-calendar-days"></i></span>
                </div>
                <div class="forms">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="" selected hidden>Select a Gender</option>
                        <option value="Male" <?php if ($patient['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($patient['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($patient['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                        <option value="Prefer not to say" <?php if ($patient['gender'] == 'Prefer not to say') echo 'selected'; ?>>Prefer not to say</option>
                    </select>
                </div>
            </div>

            <div class="forms">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo $patient['email']; ?>" required>
            </div>

            <div class="forms">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $patient['phone']; ?>" required>
            </div>

            <div class="forms">
                <label for="profile_image">Profile Image:</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*">
            </div>

            <div class="forms">
                <button type="submit" name="update">Update Profile</button>
            </div>
        </form>
    </div>

    <script>
        flatpickr("#dob", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            disableMobile: true
        });

        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#gender');

            const phoneInputField = document.querySelector("#phone");
            window.intlTelInput(phoneInputField, {
                initialCountry: "auto",
                geoIpLookup: function(success, failure) {
                    fetch('https://ipinfo.io', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then((resp) => resp.json())
                        .then((resp) => {
                            const countryCode = (resp && resp.country) ? resp.country : "us";
                            success(countryCode);
                        })
                        .catch(() => {
                            success("us");
                        });
                },
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });
        });

        // Close error message
        document.querySelector('.close-error').addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    </script>
</body>

</html>

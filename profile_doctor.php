<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated or not a doctor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['doctor_id'] ?? null;
$error_message = '';
$success_message = '';

if ($doctor_id) {
    // Fetch current doctor details
    $sql = "SELECT * FROM doctors WHERE id='$doctor_id'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $doctor = $result->fetch_assoc();
    } else {
        $error_message = "Doctor not found.";
    }

    // Handle form submissions
    if (isset($_POST['update_profile'])) {
        $full_name = $_POST['full_name'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $specialist = $_POST['specialist'];
        $hospital = $_POST['hospital'];

        // Update doctor's profile in the database
        $sql = "UPDATE doctors SET full_name='$full_name', dob='$dob', gender='$gender', email='$email', phone='$phone', specialist='$specialist', hospital='$hospital' WHERE id='$doctor_id'";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Profile updated successfully.";
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }

    // Handle profile image upload
    if (isset($_POST['upload_image'])) {
        if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === UPLOAD_ERR_OK) {
            $target_dir = "profile_images/";
            $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["profile_image"]["tmp_name"]);

            if ($check !== false) {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    $sql = "UPDATE doctors SET profile_image='$target_file' WHERE id='$doctor_id'";
                    if ($conn->query($sql) === TRUE) {
                        $success_message = "Profile image uploaded successfully.";
                        // Update session variable if needed
                        $_SESSION['profile_image'] = $target_file;
                    } else {
                        $error_message = "Error updating profile image: " . $conn->error;
                    }
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "File is not an image.";
            }
        } else {
            $error_message = "No file uploaded or an error occurred during upload.";
        }
    }
} else {
    $error_message = "Doctor ID is missing.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/profile.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="profile_all">
                <div class="title">
                    <h2>Doctor Profile</h2>
                </div>
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <p><?php echo $error_message; ?></p>
                        <span class="close-error"><i class="fa-solid fa-times"></i></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message)) : ?>
                    <div class="success-message">
                        <p><?php echo $success_message; ?></p>
                        <span class="close-success"><i class="fa-solid fa-times"></i></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($doctor)) : ?>
                    <div class="profile">
                        <form action="profile_doctor.php" method="post" enctype="multipart/form-data">
                            <div class="profile-image profile-item">
                                <label for="profile_image">Profile Image</label>
                                <?php if (!empty($doctor['profile_image'])) : ?>
                                    <img src="<?php echo $doctor['profile_image']; ?>" alt="Profile Image">
                                <?php else : ?>
                                    <img src="./profile_images/default.png" alt="Default Profile Image">
                                <?php endif; ?>
                                <input type="file" name="profile_image" id="profile_image" class="inputfile">

                                <label for="profile_image" class="custom-file-label">Click here to change the image</label>

                                <button type="submit" name="upload_image">Upload Image</button>
                            </div>
                        </form>



                        <form action="profile_doctor.php" method="post">
                            <div class="profile-info">
                                <div class="profile_flex">
                                    <div class="profile-item">
                                        <label for="full_name">Full Name:</label>
                                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($doctor['full_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="profile-item">
                                        <label for="dob">Date of Birth:</label>
                                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($doctor['dob'] ?? ''); ?>" required>
                                    </div>
                                    <div class="profile-item">
                                        <label for="gender">Gender:</label>
                                        <select id="gender" name="gender" required>
                                            <option value="Male" <?php echo ($doctor['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($doctor['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($doctor['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="profile-item">
                                        <label for="email">Email Address:</label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($doctor['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="profile-item">
                                        <label for="phone">Phone Number:</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>" required>
                                    </div>
                                    <div class="profile-item">
                                        <label for="specialist">Specialist:</label>
                                        <select id="specialist" name="specialist" required>
                                            <option value="" selected disabled>Select a Specialist</option>
                                            <option value="General Practitioner (GP)" <?php echo ($doctor['specialist'] ?? '') === 'General Practitioner (GP)' ? 'selected' : ''; ?>>General Practitioner (GP)</option>
                                            <option value="Allergist/Immunologist" <?php echo ($doctor['specialist'] ?? '') === 'Allergist/Immunologist' ? 'selected' : ''; ?>>Allergist/Immunologist</option>
                                            <option value="Anesthesiologist" <?php echo ($doctor['specialist'] ?? '') === 'Anesthesiologist' ? 'selected' : ''; ?>>Anesthesiologist</option>
                                            <option value="Cardiologist" <?php echo ($doctor['specialist'] ?? '') === 'Cardiologist' ? 'selected' : ''; ?>>Cardiologist</option>
                                            <option value="Dermatologist" <?php echo ($doctor['specialist'] ?? '') === 'Dermatologist' ? 'selected' : ''; ?>>Dermatologist</option>
                                            <option value="Endocrinologist" <?php echo ($doctor['specialist'] ?? '') === 'Endocrinologist' ? 'selected' : ''; ?>>Endocrinologist</option>
                                            <option value="Gastroenterologist" <?php echo ($doctor['specialist'] ?? '') === 'Gastroenterologist' ? 'selected' : ''; ?>>Gastroenterologist</option>
                                            <option value="Hematologist" <?php echo ($doctor['specialist'] ?? '') === 'Hematologist' ? 'selected' : ''; ?>>Hematologist</option>
                                            <option value="Infectious Disease Specialist" <?php echo ($doctor['specialist'] ?? '') === 'Infectious Disease Specialist' ? 'selected' : ''; ?>>Infectious Disease Specialist</option>
                                            <option value="Nephrologist" <?php echo ($doctor['specialist'] ?? '') === 'Nephrologist' ? 'selected' : ''; ?>>Nephrologist</option>
                                            <option value="Neurologist" <?php echo ($doctor['specialist'] ?? '') === 'Neurologist' ? 'selected' : ''; ?>>Neurologist</option>
                                            <option value="Obstetrician/Gynecologist (OB/GYN)" <?php echo ($doctor['specialist'] ?? '') === 'Obstetrician/Gynecologist (OB/GYN)' ? 'selected' : ''; ?>>Obstetrician/Gynecologist (OB/GYN)</option>
                                            <option value="Oncologist" <?php echo ($doctor['specialist'] ?? '') === 'Oncologist' ? 'selected' : ''; ?>>Oncologist</option>
                                            <option value="Ophthalmologist" <?php echo ($doctor['specialist'] ?? '') === 'Ophthalmologist' ? 'selected' : ''; ?>>Ophthalmologist</option>
                                            <option value="Orthopedic Surgeon" <?php echo ($doctor['specialist'] ?? '') === 'Orthopedic Surgeon' ? 'selected' : ''; ?>>Orthopedic Surgeon</option>
                                            <option value="Otolaryngologist (ENT)" <?php echo ($doctor['specialist'] ?? '') === 'Otolaryngologist (ENT)' ? 'selected' : ''; ?>>Otolaryngologist (ENT)</option>
                                            <option value="Pediatrician" <?php echo ($doctor['specialist'] ?? '') === 'Pediatrician' ? 'selected' : ''; ?>>Pediatrician</option>
                                            <option value="Psychiatrist" <?php echo ($doctor['specialist'] ?? '') === 'Psychiatrist' ? 'selected' : ''; ?>>Psychiatrist</option>
                                            <option value="Psychologist" <?php echo ($doctor['specialist'] ?? '') === 'Psychologist' ? 'selected' : ''; ?>>Psychologist</option>
                                            <option value="Pulmonologist" <?php echo ($doctor['specialist'] ?? '') === 'Pulmonologist' ? 'selected' : ''; ?>>Pulmonologist</option>
                                            <option value="Rheumatologist" <?php echo ($doctor['specialist'] ?? '') === 'Rheumatologist' ? 'selected' : ''; ?>>Rheumatologist</option>
                                            <option value="Urologist" <?php echo ($doctor['specialist'] ?? '') === 'Urologist' ? 'selected' : ''; ?>>Urologist</option>
                                        </select>
                                    </div>

                                    <div class="profile-item">
                                        <label for="hospital">Current Hospital:</label>
                                        <input type="text" id="hospital" name="hospital" value="<?php echo htmlspecialchars($doctor['hospital'] ?? ''); ?>" required>
                                    </div>

                                </div>
                                <div class="profile-item">
                                    <button type="submit" name="update_profile">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Close error message
            document.querySelector('.close-error').addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });

            // Close success message
            document.querySelector('.close-success').addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>

    <script>
        flatpickr("#dob", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            disableMobile: true
        });

        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#gender');
            new Choices('#specialist');

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

            // Toggle password visibility
            document.querySelector('.toggle-password').addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });

            // Close error message


            document.querySelector('.close-error').addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });

            // Close success message
            document.querySelector('.close-success').addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>

</html>
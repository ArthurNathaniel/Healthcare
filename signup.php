<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Telehaven</title>
    <?php include 'cdn.php' ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css//signup.css">
</head>

<body>
    <div class="header">
        <span><i class="fa-solid fa-arrow-left"></i> Back</span>
        <div class="logo"></div>
    </div>
    <div class="signup_all">
        <div class="signup_grid">
            <a href="register_patient.php">
                <div class="card">
                    <img src="./images/patient_icon.png" alt="">
                    <h2>Patient</h2>
                    <p>
                        Join as a Patient to access comprehensive healthcare services and personalized
                        medical care tailored to your needs.
                    </p>
                </div>
            </a>
            <a href="register_doctor.php">
            <div class="card">
            
                    <img src="./images/doctor_icon.png" alt="">
                    <h2>Doctor</h2>
                    <p>
                        Sign up as a Doctor, Access patient records,
                        schedule appointments, and streamline communication—all in one place.
                    </p>
            </div>
            </a>

        </div>

        <div class="forms">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>

</html>
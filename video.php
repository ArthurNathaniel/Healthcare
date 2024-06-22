<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php include 'cdn.php' ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/dashoard.css">
</head>

<body>
    <?php

    session_start();
    include 'db.php';

    if (!isset($_SESSION['user']) || !isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }

    $greeting = '';
    $profile_image = '';

    if ($_SESSION['role'] === 'doctor') {
        if (isset($_SESSION['doctor_id'])) {
            $doctor_id = $_SESSION['doctor_id'];
            $sql = "SELECT * FROM doctors WHERE id='$doctor_id'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $doctor = $result->fetch_assoc();
                $profile_image = !empty($doctor['profile_image']) ? "profile_images/" . $doctor['profile_image'] : "profile_images/default.png";
            } else {
                // Handle case where doctor data is not found
                $error_message = "Doctor data not found.";
            }
        } else {
            // Handle case where doctor_id session variable is not set
            $error_message = "Doctor ID not set in session.";
        }
    }

    if ($_SESSION['role'] === 'patient') {
        if (isset($_SESSION['patient_id'])) {
            $patient_id = $_SESSION['patient_id'];
            $sql = "SELECT * FROM patients WHERE id='$patient_id'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $patient = $result->fetch_assoc();
                $profile_image = !empty($patient['profile_image']) ? "profile_images/" . $patient['profile_image'] : "profile_images/default.png";
            } else {
                // Handle case where patient data is not found
                $error_message = "Patient data not found.";
            }
        } else {
            // Handle case where patient_id session variable is not set
            $error_message = "Patient ID not set in session.";
        }
    }

    // Get current hour
    date_default_timezone_set('UTC'); // Set your timezone
    $current_hour = date('G');

    // Define greeting based on the time of day
    if ($current_hour >= 5 && $current_hour < 12) {
        // $greeting = 'Maa chi!';
        $greeting = 'Good morning!';
    } elseif ($current_hour >= 12 && $current_hour < 18) {
        // $greeting = 'Maa haa!';
        $greeting = 'Good Afternoon!';
    } else {
        // $greeting = 'Maa jo!';
        $greeting = 'Good Evening!';
    }
    ?>



    <div class="page_all">
        <?php include 'sidebar.php' ?>
        <div class="context">
            <div class="search_btn">
                <div class="forms">
                    <input type="text" placeholder="Search for a podcast">
                    <span><i class="fa-solid fa-magnifying-glass"></i></span>
                </div>
            </div>
            <div class="top_grid">
                
               <div class="welcome">
                    <h4> Welcome to Telehaven - Audio Health Podcast</h4>
                    <p>
                        At Telehaven, we bridge the gap between you and your healthcare providers, offering seamless access to quality care from the comfort of your home.
                    </p>

                    <div class="welcome_btn">
                        <a href="">
                            <button>BOOK AN APPOINTMENT <span><i class="fas fa-calendar-alt"></i></span></button>
                        </a>
                    </div>

                </div> 
                <div class="profile_dashboard">
                    <?php if ($_SESSION['role'] === 'doctor') : ?>
                        <img src="<?php echo $doctor['profile_image']; ?>" alt="Profile Image">
                        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" class="profile-image">

                        <br>
                        <p><?php echo $greeting; ?><br>
                            Doctor <?php echo $_SESSION['user']; ?></p>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] === 'patient') : ?>
                        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" class="profile-image">
                        <br>
                        <p><?php echo $greeting; ?> <br>
                            Mr/Mrs. <?php echo $_SESSION['user']; ?></p>
                    <?php endif; ?>
                    <br>
                </div>

            </div>

            <?php include 'health_video_talk.php' ?>

        </div>
    </div>
    <script src="./js/swiper.js"></script>
</body>

</html>
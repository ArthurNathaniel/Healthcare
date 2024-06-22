<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Fetch available doctors and their availability
$availability = [];
$sql_availability = "SELECT da.id, da.date, da.start_time, da.end_time, da.phone_number, 
                            d.full_name AS doctor_name, d.specialist, 
                            (SELECT COUNT(*) FROM appointments a WHERE a.doctor_availability_id = da.id) AS booking_count
                     FROM doctor_availability da
                     LEFT JOIN doctors d ON da.doctor_id = d.id
                     WHERE da.date >= CURDATE()
                     ORDER BY da.date DESC, da.start_time DESC";
$result_availability = $conn->query($sql_availability);
if ($result_availability->num_rows > 0) {
    while ($row = $result_availability->fetch_assoc()) {
        $availability[] = $row;
    }
}

// Fetch doctors' profiles
$doctors = [];
$sql_doctors = "SELECT full_name, specialist, profile_image FROM doctors";
$result_doctors = $conn->query($sql_doctors);
if ($result_doctors->num_rows > 0) {
    while ($row = $result_doctors->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_SESSION['user_id'];
    $availability_id = $_POST['availability_id'];

    // Fetch the selected availability slot
    $sql_slot = $conn->prepare("SELECT * FROM doctor_availability WHERE id = ?");
    $sql_slot->bind_param("i", $availability_id);
    $sql_slot->execute();
    $result_slot = $sql_slot->get_result();
    if ($result_slot->num_rows > 0) {
        $slot = $result_slot->fetch_assoc();
        $booking_date = $slot['date'];
        $booking_time = $slot['start_time'];

        // Insert booking
        $sql_insert = $conn->prepare("INSERT INTO appointments (patient_id, doctor_availability_id, booking_date, booking_time) VALUES (?, ?, ?, ?)");
        $sql_insert->bind_param("iiss", $patient_id, $availability_id, $booking_date, $booking_time);

        if ($sql_insert->execute() === TRUE) {
            $success_message = "Appointment successfully booked.";
        } else {
            $error_message = "Error: " . $sql_insert->error;
        }

        $sql_insert->close();
    } else {
        $error_message = "Selected slot is no longer available.";
    }

    $sql_slot->close();
}

$conn->close(); // Close the database connection at the end of all operations
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="page_all">
        <?php include 'sidebar.php'; ?>
        <div class="context">
            <div class="assign_all">
                <div class="title">
                    <h2>Book an Appointment</h2>
                    <br>
                    <p>View Booked Slot <a href="patient_booked_slots.php">Click here</a></p>
                </div>
                <?php if (!empty($success_message)) : ?>
                    <div class="success-message">
                        <p><?php echo $success_message; ?></p>
                        <span class="close-success"><i class="fa-solid fa-times"></i></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <p><?php echo $error_message; ?></p>
                        <span class="close-error"><i class="fa-solid fa-times"></i></span>
                    </div>
                <?php endif; ?>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="forms">
                        <label for="availability_id">Select Available Slot:</label>
                        <select name="availability_id" id="availability_id" required>
                            <option value="">--Select Slot--</option>
                            <?php foreach ($availability as $slot) : ?>
                                <option value="<?php echo $slot['id']; ?>" <?php echo ($slot['booking_count'] > 0) ? 'disabled' : ''; ?>>
                                    <?php echo $slot['doctor_name'] . " (" . $slot['specialist'] . ") - " . $slot['date'] . " " . $slot['start_time'] . "-" . $slot['end_time'] . " (Phone: " . $slot['phone_number'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="forms">
                        <button type="submit">Book Appointment</button>
                    </div>
                </form>
            </div>
            <section>
                <style>
                    .doctors_slide{
                        display: flex;
                        flex-direction: column;
                        width: 100%;
                        text-align: left;
                        background-color: #fff;
                    }
                    .profile_doctors_all {
                        margin-top: 50px;
                    }
                    .doctors_card {
                        text-align: left;
                        display: flex;
                        flex-direction: column;
                        width: 100%;
                    }
                    .doctor_img {
                        width: 100%;
                        height: 300px !important;
                        object-fit: cover;
                    }
                    .doctors_info{
                        text-align: left;
                        width: 100%;
                        padding: 0 5%;
                        padding-block: 10px;
                    }
                    .doctors_info p{
                        margin-top: 5px;
                        display: flex;
                        gap: 10px;
                    }

                    
                    
                </style>
                <div class="profile_doctors_all">
                    <div class="profile_title">
                        <h1>Doctors Profile</h1>
                    </div>
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($doctors as $doctor) : ?>
                                <div class="swiper-slide doctors_slide">
                                    <div class="doctors_card">
                                        <img src="<?php echo $doctor['profile_image']; ?>" alt="<?php echo $doctor['full_name']; ?>" class="doctor_img">
                                    </div>
                                    <div class="doctors_info">
                                        <p> <i class="fas fa-user"></i>  <?php echo $doctor['full_name']; ?></p>
                                        <p> <i class="fas fa-stethoscope"></i> <?php echo $doctor['specialist']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="./js/swiper.js"></script>
    <script>
        // JavaScript to handle closing of messages
        document.querySelectorAll('.close-error, .close-success').forEach(element => {
            element.addEventListener('click', () => {
                element.parentElement.style.display = 'none';
            });
        });
    </script>
</body>

</html>

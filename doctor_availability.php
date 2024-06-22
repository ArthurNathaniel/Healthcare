<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Fetch doctor ID from session
$doctor_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $phone_number = $_POST['phone_number'];

    // Insert availability
    $sql_insert = $conn->prepare("INSERT INTO doctor_availability (doctor_id, date, start_time, end_time, phone_number) VALUES (?, ?, ?, ?, ?)");
    $sql_insert->bind_param("issss", $doctor_id, $date, $start_time, $end_time, $phone_number);

    if ($sql_insert->execute() === TRUE) {
        $success_message = "Availability successfully set.";
    } else {
        $error_message = "Error: " . $sql_insert->error;
    }

    $sql_insert->close();
}

$conn->close(); // Close the database connection at the end of all operations
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Availability</title>
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
                    <h2>Set Availability</h2>
                    <br>
                    <p>View my appointment slot  <a href="doctor_availability_view.php">Click Here</a></p>
                    <p>View my appointment with patients <a href="doctor_appointments_view.php">Click Here</a></p>
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
                        <label for="date">Date:</label>
                        <input type="date" name="date" id="date" required>
                    </div>
                    <div class="forms">
                        <label for="start_time">Start Time:</label>
                        <input type="time" name="start_time" id="start_time" required>
                    </div>
                    <div class="forms">
                        <label for="end_time">End Time:</label>
                        <input type="time" name="end_time" id="end_time" required>
                    </div>
                    <div class="forms">
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" name="phone_number" id="phone_number" required>
                    </div>
                    <div class="forms">
                        <button type="submit">Set Availability</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

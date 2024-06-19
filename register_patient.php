<?php
include 'db.php';

$error_message = ''; // Initialize error message variable

if (isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email already exists
    $sql = "SELECT * FROM patients WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $error_message = "Email already registered. Please use a different email or login.";
    } else {
        // Insert new patient
        $sql = "INSERT INTO patients (full_name, dob, gender, email, phone, password) 
                VALUES ('$full_name', '$dob', '$gender', '$email', '$phone', '$password')";
        if ($conn->query($sql) === TRUE) {
            // Redirect to login page
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Patient</title>
    <?php include 'cdn.php' ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="header">
        <a href="signup.php">
            <span><i class="fa-solid fa-arrow-left"></i> Back</span>
        </a>
        <div class="logo"></div>
    </div>
    <div class="register_all">
        <div class="title">
            <h2>Register as Patient</h2>
        </div>
        <?php if (!empty($error_message)): ?>
        <div class="error-message">
                <p><?php echo $error_message; ?></p>
                <span class="close-error"><i class="fa-solid fa-times"></i></span>
            </div>
            <?php endif; ?>
        <form action="register_patient.php" method="post">
            <div class="forms">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="forms_flex">
                <div class="forms">
                    <label for="dob">Date of Birth:</label>
                    <input type="text" id="dob" name="dob" required>
                    <span><i class="fa-regular fa-calendar-days"></i></span>
                </div>
                <div class="forms">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="" selected hidden>Select a Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                </div>
            </div>

            <div class="forms">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="forms">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="forms">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <span class="toggle-password"><i class="fa-regular fa-eye-slash"></i></span>
            </div>

            <div class="forms">
                <button type="submit" name="register">Register</button>
            </div>
        </form>
        <div class="forms">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

  

    <script>
        flatpickr("#dob", {
            dateFormat: "Y-m-d",
            // minDate: "today",
            maxDate: "today",
            disableMobile: true
        });
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#gender');
            // flatpickr('#dob');

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
        });
         // Close error message
         document.querySelector('.close-error').addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
    </script>
</body>

</html>
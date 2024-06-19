<?php
session_start();
include 'db.php';
$error_message = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check for Admin
    if ($email == 'admin@gmail.com' && $password == 'admin') {
        $_SESSION['user'] = 'admin';
        $_SESSION['role'] = 'admin'; // Set role for admin
        header("Location: dashboard.php");
        exit();
    } else {
        // Check for Patient
        $sql = "SELECT * FROM patients WHERE email='$email'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user'] = $row['full_name'];
                $_SESSION['role'] = 'patient'; // Set role for patient
                $_SESSION['patient_id'] = $row['id']; // Store patient id
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            // Check for Doctor
            $sql = "SELECT * FROM doctors WHERE email='$email'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user'] = $row['full_name'];
                    $_SESSION['role'] = 'doctor'; // Set role for doctor
                    $_SESSION['doctor_id'] = $row['id']; // Store doctor id
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid password.";
                }
            } else {
                $error_message = "No user found with this email.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="header">
        <span><i class="fa-solid fa-arrow-left"></i> Back</span>
        <div class="logo"></div>
    </div>
    <div class="login_all">
        <div class="title">
            <h2>Welcome back.</h2>
        </div>
        <div class="forms">
            <p>Login to continue</p>
        </div>
        <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <p><?php echo $error_message; ?></p>
            <span class="close-error"><i class="fa-solid fa-times"></i></span>
        </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="forms">
                <label for="email">Email/Username:</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="forms">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <span class="toggle-password"><i class="fa-regular fa-eye-slash"></i></span>
            </div>
            <div class="forms">
                <button type="submit" name="login">Login</button>
            </div>
            <div class="forms">
                <p>New to Telehaven? <a href="signup.php">Create an account</a></p>
            </div>
        </form>
    </div>
    <script>
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
    </script>
</body>

</html>

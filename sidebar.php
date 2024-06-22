<div class="sidebar">
    <div class="logo"></div>
    <div class="sidebar_heading">
        <h3><?php echo $_SESSION['role']; ?></h3>
    </div>
    <?php if ($_SESSION['role'] === 'patient') : ?>
  
    <div class="links">
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="profile_patient.php"><i class="fas fa-user"></i> Profile</a>
        <a href="patient_booking.php"><i class="fas fa-calendar-alt"></i> Book a Slot </a>
        <a href="view_prescriptions.php"><i class="fas fa-prescription"></i> Prescription</a>
        <a href="upload_report.php"><i class="fas fa-microscope"></i> Lab Report</a>
        <a href="podcast.php"><i class="fas fa-podcast"></i> Health Audio Podcast</a>
        <a href="video.php"><i class="fas fa-dumbbell"></i> Health Video Talk</a>
        <a href="our_blog.php"><i class="fa-regular fa-newspaper"></i> Blog</a>
    </div>
<?php endif; ?>


<?php if ($_SESSION['role'] === 'doctor') : ?>
   
    <div class="links">
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="profile_doctor.php"><i class="fas fa-user"></i> Profile</a>
        <a href="doctor_availability.php"><i class="fas fa-calendar-alt"></i> Set Appointment</a>
        <a href="patients_list.php"><i class="fas fa-prescription"></i>Write Prescription</a>
        <a href="doctor_lab.php"><i class="fas fa-microscope"></i> Assign Laboratory Test</a>
        <a href="access_patient_records.php"><i class="fas fa-folder-open"></i> Access All Patient Records</a>
        <a href="doctor_view_reports.php">report</a>
    </div>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'admin') : ?>
   
    <div class="links">
        <a href=""><i class="fas fa-user"></i> Profile</a>
        <a href=""><i class="fas fa-calendar-alt"></i> Assign Doctor to Patients</a>
        <a href="assign_patient.php"><i class="fas fa-prescription"></i> Assign Patient to Doctor</a>
        <a href=""><i class="fas fa-podcast"></i> Add Health Audio Podcast</a>
        <a href=""><i class="fas fa-dumbbell"></i> Add Training (Yoga)</a>
    </div>
<?php endif; ?>


    <div class="logout">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

</div>
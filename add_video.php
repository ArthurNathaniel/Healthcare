<?php
// Include database connection
require_once 'db.php';

// Initialize variables
$video_title = '';
$error = '';
$message = '';

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['video_file'])) {
    // Retrieve form data
    $video_title = $_POST['video_title'];

    // File details
    $file_name = $_FILES['video_file']['name'];
    $file_tmp = $_FILES['video_file']['tmp_name'];
    $file_size = $_FILES['video_file']['size'];
    $file_error = $_FILES['video_file']['error'];

    // Check if file is uploaded without errors
    if ($file_error === UPLOAD_ERR_OK) {
        // Validate file size (example: limit to 100MB)
        if ($file_size > 100 * 1024 * 1024) { // 100MB in bytes
            $error = "File size exceeds the limit (100MB).";
        } else {
            // Generate a unique name for the uploaded file
            $file_destination = './health_videos/' . $file_name;

            // Move uploaded file to destination
            if (move_uploaded_file($file_tmp, $file_destination)) {
                // File uploaded successfully, now insert into database
                $query = "INSERT INTO videos (video_url, video_title) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ss", $file_destination, $video_title);
                $stmt->execute();

                // Check for successful insertion
                if ($stmt->affected_rows > 0) {
                    $message = "Video uploaded and added successfully.";
                    // Clear input fields
                    $video_title = '';
                } else {
                    $error = "Error adding video to database.";
                }

                // Close statement
                $stmt->close();
            } else {
                $error = "Error uploading file.";
            }
        }
    } else {
        $error = "File upload error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Video</title>
</head>
<body>
    <h1>Add a New Video</h1>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif (!empty($message)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Video addition form -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="video_file">Select Video File:</label><br>
        <input type="file" id="video_file" name="video_file" required><br><br>
        
        <label for="video_title">Video Title:</label><br>
        <input type="text" id="video_title" name="video_title" value="<?php echo htmlspecialchars($video_title); ?>" required><br><br>
        
        <button type="submit">Upload Video</button>
    </form>

    <br>

    <a href="view_videos.php">View Videos</a>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>

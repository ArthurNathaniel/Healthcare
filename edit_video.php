<?php
// Include database connection
require_once 'db.php';

// Initialize variables
$video_url = '';
$video_title = '';
$error = '';
$message = '';

// Check if video ID is provided via GET parameter
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $video_id = $_GET['id'];

    // Fetch video details from database based on ID
    $query = "SELECT * FROM videos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $video = $result->fetch_assoc();
        $video_url = $video['video_url'];
        $video_title = $video['video_title'];
    } else {
        $error = "Video not found.";
    }

    // Close statement
    $stmt->close();
}

// Handle form submission for updating video details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $video_title = $_POST['video_title'];
    $video_id = $_POST['video_id'];

    // Validate input
    if (empty($video_title) || empty($video_id)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if a new video file is uploaded
        if ($_FILES['video_url']['error'] === UPLOAD_ERR_OK) {
            $video_file = $_FILES['video_url']['tmp_name'];
            $video_file_name = $_FILES['video_url']['name'];

            // Move uploaded file to a permanent location
            $upload_directory = './health_videos/';
            $new_video_url = $upload_directory . $video_file_name;

            if (move_uploaded_file($video_file, $new_video_url)) {
                // Update video details in the database with new URL
                $query = "UPDATE videos SET video_url = ?, video_title = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssi", $new_video_url, $video_title, $video_id);
                $stmt->execute();

                // Check for successful update
                if ($stmt->affected_rows > 0) {
                    $message = "Video updated successfully.";
                    // Update $video_url variable for display
                    $video_url = $new_video_url;
                } else {
                    $error = "Error updating video.";
                }

                // Close statement
                $stmt->close();
            } else {
                $error = "Error uploading video file.";
            }
        } else {
            // Update video details in the database without changing URL
            $query = "UPDATE videos SET video_title = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $video_title, $video_id);
            $stmt->execute();

            // Check for successful update
            if ($stmt->affected_rows > 0) {
                $message = "Video updated successfully.";
            } else {
                $error = "Error updating video.";
            }

            // Close statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Video</title>
    <style>
        html,
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group button {
            padding: 8px 16px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .message {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit Video</h2>

        <?php if (!empty($error)) : ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($message)) : ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="video_id" value="<?php echo htmlspecialchars($video['id']); ?>">

            <div class="form-group">
                <label for="video_url">Video File:</label>
                <input type="file" id="video_url" name="video_url">
                <p> Video: <?php echo htmlspecialchars($video_url); ?> </p>
            </div>

            <div class="form-group">
                <label for="video_title">Video Title:</label>
                <input type="text" id="video_title" name="video_title" value="<?php echo htmlspecialchars($video_title); ?>" required>
            </div>

            <div class="form-group">
                <button type="submit">Update Video</button>
            </div>
        </form>
    </div>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>

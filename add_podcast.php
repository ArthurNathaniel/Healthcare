<?php
include 'db.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "podcast/";
    
    $image = $target_dir . basename($_FILES["image"]["name"]);
    $audio = $target_dir . basename($_FILES["audio"]["name"]);
    $topic = $_POST['topic'];
    $host = $_POST['host'];
    
    // Upload files
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $image) && move_uploaded_file($_FILES["audio"]["tmp_name"], $audio)) {
        $stmt = $conn->prepare("INSERT INTO podcasts (image, audio, topic, host) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $image, $audio, $topic, $host);
        
        if ($stmt->execute()) {
            $message = "New podcast added successfully";
        } else {
            $message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $message = "Sorry, there was an error uploading your files.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Podcast</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-container h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container label, .form-container input {
            margin-bottom: 10px;
        }
        .message {
            margin-bottom: 20px;
            color: green;
        }
        .error {
            margin-bottom: 20px;
            color: red;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Add a New Podcast</h1>
        <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
        <form action="add_podcast.php" method="post" enctype="multipart/form-data">
            <label for="image">Podcast Image:</label>
            <input type="file" name="image" id="image" required>
            
            <label for="audio">Podcast Audio:</label>
            <input type="file" name="audio" id="audio" required>
            
            <label for="topic">Topic:</label>
            <input type="text" name="topic" id="topic" required>
            
            <label for="host">Host:</label>
            <input type="text" name="host" id="host" required>
            
            <input type="submit" value="Add Podcast">
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>

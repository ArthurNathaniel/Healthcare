<?php
// Include database connection
require_once 'db.php';

// Initialize an empty array to store video data
$videos = [];

// Fetch videos from the database
$query = "SELECT * FROM videos";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Fetch all rows and store in $videos array
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Videos</title>
    <style>
        html,
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
          padding: 0 7%;
            padding: 20px;
            display: flex;
            gap: 20px;
        }

        .video-item {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .video-item h3 {
            margin-top: 0;
        }

        .video-item video {
            width: 100%;
            max-width: 100%;
            height: auto;
        }

        .video-actions {
            margin-top: 10px;
        }

        .video-actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #333;
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .video-actions a:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
<h1>Manage Videos</h1>
    <div class="container">
  

        <?php if (!empty($videos)) : ?>
            <?php foreach ($videos as $video) : ?>
                <div class="video-item">
                    <h3><?php echo htmlspecialchars($video['video_title']); ?></h3>
                    <video controls>
                        <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="video-actions">
                        <a href="edit_video.php?id=<?php echo $video['id']; ?>">Edit</a>
                        <a href="delete_video.php?id=<?php echo $video['id']; ?>" onclick="return confirm('Are you sure you want to delete this video?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No videos found.</p>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>

<?php
// Include database connection
require_once 'db.php';

// Initialize variables
$error = '';
$message = '';

// Check if video ID is provided via GET parameter
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $video_id = $_GET['id'];

    // Prepare a DELETE statement
    $query = "DELETE FROM videos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $video_id);

    // Execute the statement
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "Video deleted successfully.";
        } else {
            $error = "Video not found.";
        }
    } else {
        $error = "Error deleting video.";
    }

    // Close statement
    $stmt->close();
} else {
    $error = "Video ID not provided.";
}

// Redirect back to /manage_videos.php with a message or error
if (!empty($error)) {
    // Redirect with error message
    header("Location: manage_videos.php?error=" . urlencode($error));
    exit();
} elseif (!empty($message)) {
    // Redirect with success message
    header("Location: manage_videos.php?message=" . urlencode($message));
    exit();
}
?>

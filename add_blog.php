<?php
include 'db.php';

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = $_FILES['image']['name'];
    $target = "blogs/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // Using prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO blogs (title, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $image);

        if ($stmt->execute()) {
            echo "New blog post created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to upload image";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Blog Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input, textarea, button {
            padding: 10px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Blog Post</h1>
        <form action="add_blog.php" method="post" enctype="multipart/form-data">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
            
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="5" required></textarea>
            
            <label for="image">Image</label>
            <input type="file" id="image" name="image" accept="image/*" required>
            
            <button type="submit" name="submit">Add Blog Post</button>
        </form>
    </div>
</body>
</html>

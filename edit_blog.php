<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Blog post not found.";
        exit();
    }

    $stmt->close();
} else {
    echo "No blog post ID provided.";
    exit();
}

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name'];
        $target = "blogs/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        $image = $row['image'];
    }

    $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $image, $id);

    if ($stmt->execute()) {
        echo "Blog post updated successfully";
        header("Location: manage_blogs.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Blog Post</title>
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
        <h1>Edit Blog Post</h1>
        <form action="edit_blog.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
            
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="5" required><?php echo htmlspecialchars($row['content']); ?></textarea>
            
            <label for="image">Image</label>
            <input type="file" id="image" name="image" accept="image/*">
            <p>Current image: <?php echo htmlspecialchars($row['image']); ?></p>
            
            <button type="submit" name="submit">Update Blog Post</button>
        </form>
    </div>
</body>
</html>

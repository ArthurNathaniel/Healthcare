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

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($row['title']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .blog_container {
            max-width: 800px;
            margin: auto;
        }
        .blog_image {
            max-width: 100%;
            height: auto;
        }
        .blog_content {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="blog_container">
        <h1><?php echo htmlspecialchars($row['title']); ?></h1>
        <img src="blogs/<?php echo htmlspecialchars($row['image']); ?>" alt="Blog Image" class="blog_image">
        <div class="blog_content">
            <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
        </div>
    </div>
</body>
</html>

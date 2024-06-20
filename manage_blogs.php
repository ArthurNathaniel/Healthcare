<?php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Blog Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        a {
            text-decoration: none;
            color: #007BFF;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Blog Posts</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM blogs ORDER BY created_at DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . $row['id'] . '</td>
                                <td>' . htmlspecialchars($row['title']) . '</td>
                                <td>
                                    <a href="edit_blog.php?id=' . $row['id'] . '">Edit</a> | 
                                    <a href="delete_blog.php?id=' . $row['id'] . '" onclick="return confirm(\'Are you sure you want to delete this blog?\')">Delete</a>
                                </td>
                              </tr>';
                    }
                } else {
                    echo '<tr><td colspan="3">No blog posts found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

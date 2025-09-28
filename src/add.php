<?php
// add.php – Add a new book
require 'config.php'; // database connection

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $publisher = trim($_POST['publisher']);
    $year = $_POST['year_published'];
    $isbn = trim($_POST['isbn']);
    $copies = $_POST['copies'];

    $sql = "INSERT INTO books (title, author, publisher, year_published, isbn, copies, available) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $available = $copies;
        $stmt->bind_param("sssssis", $title, $author, $publisher, $year, $isbn, $copies, $available);

        if ($stmt->execute()) {
            header("Location: index.php?msg=Book+added+successfully");
            exit;
        } else {
            $message = "❌ Error adding book: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "❌ Failed to prepare statement: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Book</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 25px 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
        }

        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #34495e;
        }

        form input,
        form button {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 8px;
            border: 1.5px solid #d0d7de;
            font-size: 1rem;
        }

        form input:focus {
            border-color: #2980b9;
            outline: none;
            box-shadow: 0 0 4px rgba(41, 128, 185, 0.4);
        }

        form button {
            background-color: #2980b9;
            color: #fff;
            font-weight: 600;
            border: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        form button:hover,
        form button:focus {
            background-color: #1c5980;
            cursor: pointer;
            box-shadow: 0 6px 12px rgba(28, 89, 128, 0.4);
        }

        .message {
            max-width: 500px;
            margin: 15px auto;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
        }

        .message.error {
            background: #ffe0e0;
            color: #c0392b;
            border: 1px solid #e74c3c;
        }
    </style>
</head>

<body>
    <h1>Add a New Book</h1>

    <a href="index.php" class="home-btn">🏠 Home</a>

    <?php if (!empty($message)): ?>
        <div class="message error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="author">Author:</label>
        <input type="text" name="author" id="author">

        <label for="publisher">Publisher:</label>
        <input type="text" name="publisher" id="publisher">

        <label for="year_published">Year Published:</label>
        <input type="number" name="year_published" id="year_published" min="1500" max="2099">

        <label for="isbn">ISBN:</label>
        <input type="text" name="isbn" id="isbn">

        <label for="copies">Copies:</label>
        <input type="number" name="copies" id="copies" value="1" min="1">

        <button type="submit">➕ Add Book</button>
    </form>
</body>

</html>
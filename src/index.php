<?php
// index.php – Prototype for Lab Exam
require 'config.php'; // database connection file

// Fetch all books
$sql = "SELECT * FROM books";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1> Library Management System</h1>

    <!-- Navigation -->
    <nav>
        <a href="add_book.php">➕ Add Book</a>
        <a href="borrow_book.php">📖 Borrow Book</a>
        <a href="return_book.php">🔄 Return Book</a>
        <a href="search.php">🔍 Search Books</a>
    </nav>

    <!-- Books Table -->
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Publisher</th>
                <th>Year</th>
                <th>ISBN</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['publisher']) ?></td>
                        <td><?= htmlspecialchars($book['year_published']) ?></td>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <td><?= htmlspecialchars($book['available']) ?></td>
                        <td class="actions">
                            <a href="edit_book.php?id=<?= $book['id'] ?>" title="Edit Book">✏️ Edit</a>
                            <a href="delete_book.php?id=<?= $book['id'] ?>" onclick="return confirm('Are you sure you want to delete this book?')" title="Delete Book">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty">No books found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
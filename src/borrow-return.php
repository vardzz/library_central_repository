<?php
require 'config.php'; // DB connection ($mysqli)

// Success message
$message = "";

// Handle Borrow
if (isset($_POST['borrow_id']) && !empty($_POST['borrower_name'])) {
    $book_id = intval($_POST['borrow_id']);
    $borrower = trim($_POST['borrower_name']);
    $due_date = date('Y-m-d', strtotime('+7 days'));

    // Insert borrowing record
    $stmt = $mysqli->prepare("INSERT INTO borrowings (book_id, borrower_name, due_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $book_id, $borrower, $due_date);
    $stmt->execute();

    // Update available copies
    $mysqli->query("UPDATE books SET available = available - 1 WHERE id = $book_id");

    $message = "✅ Book borrowed successfully by $borrower!";
}

// Handle Return
if (isset($_POST['return_id'])) {
    $borrowing_id = intval($_POST['return_id']);

    // Get book id from borrowing record
    $res = $mysqli->query("SELECT book_id, borrower_name FROM borrowings WHERE id = $borrowing_id");
    $row = $res->fetch_assoc();
    $book_id = $row['book_id'];
    $borrower = $row['borrower_name'];

    // Update borrowing record
    $mysqli->query("UPDATE borrowings SET status='returned', returned_at=NOW() WHERE id=$borrowing_id");

    // Update available copies
    $mysqli->query("UPDATE books SET available = available + 1 WHERE id = $book_id");

    $message = "✅ $borrower successfully returned the book!";
}

// Fetch available books
$available_books = $mysqli->query("SELECT * FROM books WHERE available > 0")->fetch_all(MYSQLI_ASSOC);

// Fetch borrowed books
$borrowed_books = $mysqli->query("
    SELECT br.id AS borrowing_id, bk.title, bk.author, br.borrower_name, br.due_date 
    FROM borrowings br
    JOIN books bk ON br.book_id = bk.id
    WHERE br.status='borrowed'
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Borrow - Return Books</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Home Button -->
    <a href="index.php" class="home-btn">🏠 Home</a>

    <h1> Borrow - Return Books</h1>

    <!-- Success Message -->
    <?php if (!empty($message)): ?>
        <p style="text-align:center; color: green; font-weight:bold; margin-bottom:20px;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <!-- Borrow Table -->
    <h2>Available Books to Borrow</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Publisher</th>
                <th>Year</th>
                <th>ISBN</th>
                <th>Available</th>
                <th>Your Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($available_books) > 0): ?>
                <?php foreach ($available_books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['publisher']) ?></td>
                        <td><?= htmlspecialchars($book['year_published']) ?></td>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <td><?= htmlspecialchars($book['available']) ?></td>
                        <td>
                            <form method="post" style="display:flex; gap:8px; margin:0;">
                                <input type="text" name="borrower_name" placeholder="Enter name" required>
                                <input type="hidden" name="borrow_id" value="<?= $book['id'] ?>">
                                <button type="submit">Borrow</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty">No available books to borrow.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Return Table -->
    <h2 style="margin-top:40px;">Borrowed Books (to Return)</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Borrower</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($borrowed_books) > 0): ?>
                <?php foreach ($borrowed_books as $borrowed): ?>
                    <tr>
                        <td><?= htmlspecialchars($borrowed['title']) ?></td>
                        <td><?= htmlspecialchars($borrowed['author']) ?></td>
                        <td><?= htmlspecialchars($borrowed['borrower_name']) ?></td>
                        <td><?= htmlspecialchars($borrowed['due_date']) ?></td>
                        <td>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="return_id" value="<?= $borrowed['borrowing_id'] ?>">
                                <button type="submit">Return</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty">No borrowed books at the moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
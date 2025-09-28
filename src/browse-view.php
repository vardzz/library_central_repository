<?php
require 'config.php';

// ===== AJAX DELETE HANDLER =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $mysqli->prepare("DELETE FROM books WHERE id=?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<span class='message success'>✅ Book deleted successfully!</span>";
    } else {
        echo "<span class='message error'>❌ Error deleting book: {$stmt->error}</span>";
    }
    exit; // Stop the rest of the page from rendering for AJAX
}

// ===== EDIT HANDLER =====
$message = "";
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($action === 'edit') {
    if (!$id) die("Invalid book ID.");

    // Fetch existing book data
    $stmt = $mysqli->prepare("SELECT * FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $publisher = trim($_POST['publisher']);
        $year = $_POST['year_published'];
        $isbn = trim($_POST['isbn']);
        $copies = intval($_POST['copies']);
        $available = intval($_POST['available']);

        $stmt = $mysqli->prepare("UPDATE books SET title=?, author=?, publisher=?, year_published=?, isbn=?, copies=?, available=? WHERE id=?");
        $stmt->bind_param("sssssiii", $title, $author, $publisher, $year, $isbn, $copies, $available, $id);

        if ($stmt->execute()) {
            $message = "✅ Book updated successfully!";
            $book = [
                'title' => $title,
                'author' => $author,
                'publisher' => $publisher,
                'year_published' => $year,
                'isbn' => $isbn,
                'copies' => $copies,
                'available' => $available
            ];
        } else {
            $message = "❌ Error updating book: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Book </title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .edit-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
        }

        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 25px;
            color: #1e293b;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #34495e;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 8px;
            border: 1.5px solid #d0d7de;
            font-size: 1rem;
        }

        input:focus {
            border-color: #2980b9;
            outline: none;
            box-shadow: 0 0 4px rgba(41, 128, 185, 0.4);
        }

        button {
            width: 100%;
            padding: 12px 0;
            background-color: #2980b9;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(41, 128, 185, 0.25);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        button:hover,
        button:focus {
            background-color: #1c5980;
            box-shadow: 0 6px 12px rgba(28, 89, 128, 0.4);
        }

        .message {
            text-align: center;
            font-weight: 600;
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
        }

        .message.success {
            background: #e0f8e0;
            color: #27ae60;
            border: 1px solid #27ae60;
        }

        .message.error {
            background: #ffe0e0;
            color: #c0392b;
            border: 1px solid #e74c3c;
        }

        .deleted-message {
            text-align: center;
            font-size: 1.1rem;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 20px;
                margin: 30px auto;
            }

            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <a href="index.php" class="home-btn">🏠 Home</a>

    <div class="edit-container">
        <h1>Edit Book</h1>

        <?php if (!empty($message)): ?>
            <p class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if ($action === 'edit' && isset($book)): ?>
            <form method="post">
                <label>Title:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>

                <label>Author:</label>
                <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>">

                <label>Publisher:</label>
                <input type="text" name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>">

                <label>Year Published:</label>
                <input type="number" name="year_published" value="<?= htmlspecialchars($book['year_published']) ?>" min="1500" max="2099">

                <label>ISBN:</label>
                <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">

                <label>Copies:</label>
                <input type="number" name="copies" value="<?= htmlspecialchars($book['copies']) ?>" min="1">

                <label>Available:</label>
                <input type="number" name="available" value="<?= htmlspecialchars($book['available']) ?>" min="0">

                <button type="submit" name="update">💾 Update Book</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
Jericho
<?php
require 'config.php';

// Fetch all books
$sql = "SELECT * FROM books";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);

// Generate table rows
if (count($books) > 0) {
    foreach ($books as $book) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($book['title']) . '</td>';
        echo '<td>' . htmlspecialchars($book['author']) . '</td>';
        echo '<td>' . htmlspecialchars($book['publisher']) . '</td>';
        echo '<td>' . htmlspecialchars($book['year_published']) . '</td>';
        echo '<td>' . htmlspecialchars($book['isbn']) . '</td>';
        echo '<td>' . htmlspecialchars($book['available']) . '</td>';
        echo '<td class="actions">';
        echo '<a href="edit-remove.php?action=edit&id=' . $book['id'] . '">✏️ Edit</a>';
        echo ' <button class="delete-btn" data-id="' . $book['id'] . '">🗑️ Delete</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" class="empty">No books found.</td></tr>';
}
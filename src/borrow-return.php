<?php
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}


require 'config.php';


$role = $_SESSION['role'];


// ===== READER LOGIC =====
if ($role === 'reader') {
    $mode = isset($_GET['action']) && $_GET['action'] === 'return' ? 'return' : 'borrow';


    // ===== HANDLE FORM SUBMISSION =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $borrower_name = trim($_POST['borrower_name']);


        if ($mode === 'borrow') {
            $book_id = intval($_POST['book_id']);
            $duration = min(max(intval($_POST['duration']), 1), 7);


            $check = $mysqli->prepare("SELECT available FROM books WHERE id=?");
            $check->bind_param("i", $book_id);
            $check->execute();
            $check->bind_result($available);
            $check->fetch();
            $check->close();


            if ($available > 0) {
                $due_date = date("Y-m-d", strtotime("+$duration days"));


                $stmt = $mysqli->prepare("INSERT INTO borrowings (book_id, borrower_name, due_date) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $book_id, $borrower_name, $due_date);
                $stmt->execute();
                $stmt->close();


                $update = $mysqli->prepare("UPDATE books SET available = available - 1 WHERE id=?");
                $update->bind_param("i", $book_id);
                $update->execute();
                $update->close();


                $_SESSION['message'] = "Book successfully borrowed!";
            } else {
                $_SESSION['message'] = "No available copies left.";
            }
        }


        if ($mode === 'return') {
            $borrow_id = intval($_POST['borrow_id']);


            $stmt = $mysqli->prepare("SELECT book_id, borrower_name FROM borrowings WHERE id=? AND status='borrowed'");
            $stmt->bind_param("i", $borrow_id);
            $stmt->execute();
            $stmt->bind_result($book_id, $record_name);
            $stmt->fetch();
            $stmt->close();


            if ($book_id && strcasecmp($borrower_name, $record_name) === 0) {
                $stmt = $mysqli->prepare("UPDATE borrowings SET status='returned', returned_at=NOW() WHERE id=?");
                $stmt->bind_param("i", $borrow_id);
                $stmt->execute();
                $stmt->close();


                $update = $mysqli->prepare("UPDATE books SET available = available + 1 WHERE id=?");
                $update->bind_param("i", $book_id);
                $update->execute();
                $update->close();


                $_SESSION['message'] = "Book successfully returned!";
            } else {
                $_SESSION['message'] = "Incorrect borrower name. Return failed.";
            }
        }


        header("Location: borrow-return.php?action=$mode");
        exit;
    }


    // ===== FETCH DATA =====
    if ($mode === 'borrow') {
        $sql = "SELECT * FROM books ORDER BY title ASC";
    } else {
        $sql = "SELECT b.id, b.title, b.author, b.publisher, b.year_published, b.isbn,
                       br.due_date, br.id AS borrow_id
                FROM borrowings br
                JOIN books b ON br.book_id = b.id
                WHERE br.status = 'borrowed'
                ORDER BY b.title ASC";
    }
    $result = $mysqli->query($sql);
}


// ===== LIBRARIAN LOGIC =====
if ($role === 'librarian') {
    $sqlBorrowed = "SELECT b.id, b.title, b.author, b.publisher, b.year_published, b.isbn,
                           br.borrower_name, br.due_date, br.borrowed_at
                    FROM borrowings br
                    JOIN books b ON br.book_id = b.id
                    WHERE br.status='borrowed'
                    ORDER BY br.due_date ASC";
    $resultBorrowed = $mysqli->query($sqlBorrowed);


    $sqlReturned = "SELECT b.id, b.title, b.author, b.publisher, b.year_published, b.isbn,
                           br.borrower_name, br.due_date, br.returned_at
                    FROM borrowings br
                    JOIN books b ON br.book_id = b.id
                    WHERE br.status='returned'
                    ORDER BY br.returned_at DESC";
    $resultReturned = $mysqli->query($sqlReturned);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($role); ?> - Borrow/Return</title>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .borrow-btn {
            background: #4CAF50;
            color: white;
        }

        .return-btn {
            background: #2196F3;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 18px;
        }

        h2 {
            margin-top: 30px;
        }

        table {
            margin-bottom: 40px;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <h1><?php echo ucfirst($role); ?> Borrow/Return</h1>
        <div>
            <a href="<?php echo $role === 'reader' ? 'reader.php' : 'librarian.php'; ?>"><button>Back</button></a>
            <a href="logout.php"><button>Logout</button></a>
        </div>
    </div>


    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <script>
                alert("<?php echo addslashes($_SESSION['message']); ?>");
            </script>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>


        <?php if ($role === 'reader'): ?>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Year</th>
                        <th>Publisher</th>
                        <?php if ($mode === 'borrow'): ?>
                            <th>Available Copies</th>
                            <th>Status</th>
                        <?php else: ?>
                            <th>Due Date</th>
                        <?php endif; ?>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['title']); ?></strong><br><small>ISBN: <?= htmlspecialchars($row['isbn']); ?></small></td>
                                <td><?= htmlspecialchars($row['author']); ?></td>
                                <td><?= htmlspecialchars($row['year_published']); ?></td>
                                <td><?= htmlspecialchars($row['publisher']); ?></td>
                                <?php if ($mode === 'borrow'): ?>
                                    <td><?= $row['available'] . '/' . $row['copies']; ?><br>
                                        <small><?= $row['available'] == $row['copies'] ? 'All available' : 'Partially available'; ?></small>
                                    </td>

                                    <?php
                                    if ($row['available'] == $row['copies']) {
                                        $statusClass = 'available';
                                        $statusText = 'Available';
                                    } elseif ($row['available'] == 0) {
                                        $statusClass = 'unavailable';
                                        $statusText = 'Unavailable';
                                    } else {
                                        $statusClass = 'partial';
                                        $statusText = 'Partially Available';
                                    }
                                    ?>
                                    <td><span class="status <?= $statusClass; ?>"><?= $statusText; ?></span></td>


                                <?php else: ?>
                                    <td><?= htmlspecialchars($row['due_date']); ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($mode === 'borrow'): ?>
                                        <?php if ($row['available'] > 0): ?>
                                            <button class="action-btn borrow-btn"
                                                data-id="<?= $row['id']; ?>"
                                                data-title="<?= htmlspecialchars($row['title']); ?>">
                                                Borrow
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="action-btn return-btn"
                                            data-id="<?= $row['borrow_id']; ?>"
                                            data-title="<?= htmlspecialchars($row['title']); ?>">
                                            Return
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $mode === 'borrow' ? 7 : 6; ?>">No books found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>


            <!-- MODAL -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3 id="modalTitle"></h3>
                    <form method="post" action="borrow-return.php?action=<?php echo $mode; ?>">
                        <input type="hidden" name="book_id" id="bookId">
                        <?php if ($mode === 'return'): ?><input type="hidden" name="borrow_id" id="borrowId"><?php endif; ?>
                        <label>Your Name:</label>
                        <input type="text" name="borrower_name" required><br><br>
                        <?php if ($mode === 'borrow'): ?>
                            <label>Duration (days, max 7):</label>
                            <input type="number" name="duration" min="1" max="7" required><br><br>
                        <?php endif; ?>
                        <button type="submit" name="submit"><?= ucfirst($mode); ?></button>
                    </form>
                </div>
            </div>


        <?php elseif ($role === 'librarian'): ?>
            <h2>Currently Borrowed Books</h2>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Year</th>
                        <th>Publisher</th>
                        <th>Borrower</th>
                        <th>Due Date</th>
                        <th>Borrowed At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultBorrowed && $resultBorrowed->num_rows > 0): ?>
                        <?php while ($row = $resultBorrowed->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['title']); ?></strong><br><small>ISBN: <?= htmlspecialchars($row['isbn']); ?></small></td>
                                <td><?= htmlspecialchars($row['author']); ?></td>
                                <td><?= htmlspecialchars($row['year_published']); ?></td>
                                <td><?= htmlspecialchars($row['publisher']); ?></td>
                                <td><?= htmlspecialchars($row['borrower_name']); ?></td>
                                <td><?= htmlspecialchars($row['due_date']); ?></td>
                                <td><?= htmlspecialchars($row['borrowed_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No borrowed books</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>


            <h2>Returned Books</h2>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Year</th>
                        <th>Publisher</th>
                        <th>Borrower</th>
                        <th>Due Date</th>
                        <th>Returned At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultReturned && $resultReturned->num_rows > 0): ?>
                        <?php while ($row = $resultReturned->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['title']); ?></strong><br><small>ISBN: <?= htmlspecialchars($row['isbn']); ?></small></td>
                                <td><?= htmlspecialchars($row['author']); ?></td>
                                <td><?= htmlspecialchars($row['year_published']); ?></td>
                                <td><?= htmlspecialchars($row['publisher']); ?></td>
                                <td><?= htmlspecialchars($row['borrower_name']); ?></td>
                                <td><?= htmlspecialchars($row['due_date']); ?></td>
                                <td><?= htmlspecialchars($row['returned_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No returned books</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>


    <script>
        const modal = document.getElementById('modal');
        const modalTitle = document.getElementById('modalTitle');
        const bookIdInput = document.getElementById('bookId');
        const borrowIdInput = document.getElementById('borrowId');


        document.querySelectorAll('.borrow-btn, .return-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const title = btn.getAttribute('data-title');
                modalTitle.textContent = (btn.classList.contains('borrow-btn') ? "Borrow: " : "Return: ") + title;
                bookIdInput.value = btn.getAttribute('data-id');
                if (borrowIdInput) borrowIdInput.value = btn.getAttribute('data-id');
                modal.style.display = 'flex';
            });
        });


        function closeModal() {
            modal.style.display = 'none';
        }
    </script>
</body>

</html>
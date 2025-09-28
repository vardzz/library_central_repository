<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'librarian') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard</title>
    <!-- Google Fonts: Customized for library theme -->
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <h1>Librarian Dashboard</h1>
        <div>
            <button onclick="document.getElementById('addModal').style.display='block'">Add New Book</button>
            <a href="borrow-return.php"><button>Manage Borrow and Returns</button></a>
            <a href="logout.php"><button>Logout</button></a>
        </div>
    </div>

    <div class="container">
        <?php include 'browse-view.php'; ?> <!-- Handles search, table, and JS -->
    </div>

    <!-- Show Session Messages (from add.php, delete.php, etc.) -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>alert("<?php echo addslashes($_SESSION['message']); ?>");</script>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Add Book Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h2>Add New Book</h2>
            <form method="POST" action="add.php">
                <input type="text" name="title" placeholder="Book Title" required>
                <input type="text" name="author" placeholder="Author" required>
                <input type="text" name="publisher" placeholder="Publisher" required>
                <input type="text" name="isbn" placeholder="ISBN" required>
                <input type="number" name="year" placeholder="Publishing Year" min="1000" max="9999" required>
                <input type="number" name="copies" placeholder="Number of Copies" min="1" required>
                <button type="submit" name="add_book" value="1">Add New Book</button>
            </form>
        </div>
    </div>

    <script>
        // Modal close on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

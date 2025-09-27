<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <a href="index.php" class="home-btn">🏠 Home</a>
    <h1>Library Management System</h1>

    <div id="ajax-message" style="text-align:center; margin-bottom:20px; font-weight:bold;"></div>

    <nav>
        <a href="add.php">➕ Add Book</a>
        <a href="borrow-return.php">📖 Borrow / Return Book</a>
    </nav>

    <!-- Search Bar -->
    <form id="search-form" style="text-align:center; margin-bottom: 20px;">
        <input type="text" name="q" id="search-input" placeholder="Search by title, author, or ISBN"
            style="padding: 10px; width: 60%; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem;">
        <button type="submit">🔍 Search</button>
    </form>

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
        <tbody id="books-table">
            <!-- Table rows loaded via AJAX -->
        </tbody>
    </table>

    <script>
        const booksTable = document.getElementById('books-table');
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');

        // Function to load initial books
        function loadBooks() {
            fetch('browse-view.php')
                .then(res => res.text())
                .then(html => booksTable.innerHTML = html)
                .catch(err => console.error(err));
        }

        // Load books initially
        loadBooks();

        // Search functionality
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            const query = searchInput.value;

            fetch('search.php?q=' + encodeURIComponent(query))
                .then(res => res.text())
                .then(html => booksTable.innerHTML = html)
                .catch(err => console.error(err));
        });

        // Delete book functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-btn')) {
                const btn = e.target;
                const bookId = btn.dataset.id;
                if (confirm("Are you sure you want to delete this book?")) {
                    fetch('edit-remove.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'delete_id=' + bookId
                        })
                        .then(res => res.text())
                        .then(data => {
                            document.getElementById('ajax-message').innerHTML = data;
                            btn.closest('tr').remove();
                        })
                        .catch(err => console.error(err));
                }
            }
        });
    </script>
</body>

</html>
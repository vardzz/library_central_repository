<?php
// browse-view.php: Shared view for displaying books table, customized by user role
// Updated with Edit Modal (AJAX integration with edit-remove.php)

require 'config.php';

// ===== FETCH ALL BOOKS FROM DB =====
$sql = "SELECT id, title, author, publisher, year_published AS year, isbn, copies, available
        FROM books ORDER BY LOWER(title) ASC";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


$role = $_SESSION['role'] ?? 'reader';
$isLibrarian = ($role === 'librarian');
?>


<!-- Shared Search Bar -->
<div class="search-bar">
    <input type="text" id="search" placeholder="Search books...">
    <button onclick="searchBooks()">Search</button>
</div>


<!-- Dynamic Table -->
<table class="library-table" id="booksTable">
    <thead>
        <tr>
            <?php if ($isLibrarian): ?>
                <th>Book</th>
                <th>Author</th>
                <th>Year</th>
                <th>Publisher</th>
                <th>Copies</th>
                <th>Status</th>
                <th>Actions</th>
            <?php else: ?>
                <th>Book</th>
                <th>Author</th>
                <th>Year</th>
                <th>Publisher</th>
                <th>Available Copies</th>
                <th>Status</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (count($books) > 0):
            foreach ($books as $book):
                if ($book['available'] == $book['copies']) {
                    $statusClass = 'available';
                    $statusText = 'Available';
                } elseif ($book['available'] == 0) {
                    $statusClass = 'unavailable';
                    $statusText = 'Unavailable';
                } else {
                    $statusClass = 'partial';
                    $statusText = 'Partially Available';
                }



                // Unified text format for copies
                $copiesText = $book['available'] . '/' . $book['copies'] .
                    ($book['available'] == $book['copies']
                        ? '<br><small>all available</small>'
                        : '<br><small>' . $book['available'] . ' available</small>');
        ?>
                <tr>
                    <td>
                        <div class="book-cell">
                            <div class="book-info">
                                <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                <small>ISBN: <?php echo htmlspecialchars($book['isbn']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></td>
                    <td><?php echo htmlspecialchars($book['year'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($book['publisher'] ?? 'Unknown Publisher'); ?></td>


                    <?php if ($isLibrarian): ?>
                        <td><strong><?php echo $copiesText; ?></strong></td>
                        <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                        <td class="actions">
                            <div class="action-buttons">
                                <!-- Edit Button -->
                                <button class="btn btn-edit" onclick="openEditModal(<?php echo $book['id']; ?>)">
                                    Edit
                                </button>
                                <!-- Delete Button -->
                                <button class="btn btn-delete delete-btn" onclick="deleteBook(<?php echo $book['id']; ?>)">
                                    Delete
                                </button>
                            </div>
                        </td>
                    <?php else: ?>
                        <td><strong><?php echo $copiesText; ?></strong></td>
                        <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach;
        else: ?>
            <tr>
                <td colspan="7" class="empty">No books found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<?php if ($isLibrarian): ?>
    <!-- ===== Edit Modal ===== -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Book</h2>
            <form id="editForm">
                <input type="hidden" name="id" id="edit-id">
                <input type="text" name="title" id="edit-title" placeholder="Book Title" required>
                <input type="text" name="author" id="edit-author" placeholder="Author" required>
                <input type="number" name="year_published" id="edit-year" placeholder="Publishing Year" min="1000" max="9999" required>
                <input type="text" name="publisher" id="edit-publisher" placeholder="Publisher" required>
                <input type="text" name="isbn" id="edit-isbn" placeholder="ISBN" required>
                <input type="number" name="copies" id="edit-copies" placeholder="Number of Copies" min="1" required>
                <input type="number" name="available" id="edit-available" placeholder="Available Copies" min="0" required>
                <button type="submit">Update Book</button>
            </form>
        </div>
    </div>
<?php endif; ?>


<!-- JS -->
<script>
    function searchBooks() {
        const input = document.getElementById('search').value.toLowerCase();
        const rows = document.querySelectorAll('#booksTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }


    <?php if ($isLibrarian): ?>

        function deleteBook(id) {
            // Step 1: Ask backend for confirmation info
            fetch('edit-remove.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'confirm_delete=' + id
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'confirm' && data.action === 'delete') {
                        const book = data.summary;


                        // Build a nice summary (replace with modal if you want)
                        let summary = `
Title: ${book.title}
Author: ${book.author}
Publisher: ${book.publisher}
Year: ${book.year_published}
ISBN: ${book.isbn}
Copies: ${book.copies}
Available: ${book.available}
                `;


                        if (confirm(`${data.message}\n\n${summary}`)) {
                            // Step 2: If user confirms, actually delete
                            fetch('edit-remove.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'delete_id=' + id
                                })
                                .then(r => r.json())
                                .then(result => {
                                    alert(result.message);
                                    if (result.status === 'success') location.reload();
                                });
                        }
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => alert('Error: ' + err));
        }


        // Open Edit Modal
        function openEditModal(id) {
            fetch('edit-remove.php?id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const book = data.data;
                        document.getElementById('edit-id').value = book.id;
                        document.getElementById('edit-title').value = book.title;
                        document.getElementById('edit-author').value = book.author;
                        document.getElementById('edit-year').value = book.year_published;
                        document.getElementById('edit-publisher').value = book.publisher;
                        document.getElementById('edit-isbn').value = book.isbn;
                        document.getElementById('edit-copies').value = book.copies;
                        document.getElementById('edit-available').value = book.available;
                        document.getElementById('editModal').style.display = 'block';
                    } else {
                        alert(data.message);
                    }
                });
        }


        // Submit Edit Form via AJAX
        document.getElementById('editForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('edit-remove.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') {
                        location.reload();
                    }
                })
                .catch(err => alert('Error: ' + err));
        });


        // Modal close on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    <?php endif; ?>
</script>


<?php
$mysqli->close();
?>
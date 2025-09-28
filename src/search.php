<?php
require 'config.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($query === '') {
    $sql = "SELECT * FROM books";
    $stmt = $mysqli->prepare($sql);
} else {
    $sql = "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?";
    $stmt = $mysqli->prepare($sql);
    $like = "%$query%";
    $stmt->bind_param("sss", $like, $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);

if (count($books) === 0) {
    echo '<tr><td colspan="7" class="empty">No books found.</td></tr>';
} else {
    foreach ($books as $book) {
        echo '<tr>
            <td>' . htmlspecialchars($book['title']) . '</td>
            <td>' . htmlspecialchars($book['author']) . '</td>
            <td>' . htmlspecialchars($book['publisher']) . '</td>
            <td>' . htmlspecialchars($book['year_published']) . '</td>
            <td>' . htmlspecialchars($book['isbn']) . '</td>
            <td>' . htmlspecialchars($book['available']) . '</td>
            <td class="actions">
                <a href="edit-remove.php?action=edit&id=' . $book['id'] . '">✏️ Edit</a>
                <button class="delete-btn" data-id="' . $book['id'] . '">🗑️ Delete</button>
            </td>
        </tr>';
    }
}

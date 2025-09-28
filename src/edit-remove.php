<?php
require 'config.php';
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Invalid request"];

// ===== FETCH BOOK DATA FOR EDIT MODAL =====
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($book) {
        $response = ["status" => "success", "data" => $book];
    } else {
        $response = ["status" => "error", "message" => "Book not found"];
    }
    echo json_encode($response);
    exit;
}

// ===== REQUEST CONFIRMATION BEFORE DELETE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $id = intval($_POST['confirm_delete']);
    $stmt = $mysqli->prepare("SELECT title, author, publisher, year_published, isbn, copies, available FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($book) {
        $response = [
            "status" => "confirm",
            "action" => "delete",
            "message" => "Are you sure you want to delete this book?",
            "summary" => $book
        ];
    } else {
        $response = ["status" => "error", "message" => "Book not found"];
    }
    echo json_encode($response);
    exit;
}

// ===== ACTUAL DELETE BOOK =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $mysqli->prepare("DELETE FROM books WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "✅ Book deleted successfully!"];
    } else {
        $response = ["status" => "error", "message" => "❌ Error deleting book: " . $stmt->error];
    }
    $stmt->close();
    echo json_encode($response);
    exit;
}

// ===== REQUEST CONFIRMATION BEFORE UPDATE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_update'])) {
    $id = intval($_POST['confirm_update']);
    $stmt = $mysqli->prepare("SELECT title, author, publisher, year_published, isbn, copies, available FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($book) {
        $response = [
            "status" => "confirm",
            "action" => "update",
            "message" => "Are you sure you want to update this book?",
            "summary" => $book
        ];
    } else {
        $response = ["status" => "error", "message" => "Book not found"];
    }
    echo json_encode($response);
    exit;
}

// ===== ACTUAL UPDATE BOOK =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id        = intval($_POST['id']);
    $title     = trim($_POST['title']);
    $author    = trim($_POST['author']);
    $publisher = trim($_POST['publisher']);
    $year      = intval($_POST['year_published']);
    $isbn      = trim($_POST['isbn']);
    $copies    = intval($_POST['copies']);
    $available = intval($_POST['available']);

    // ===== ISBN VALIDATION =====
    if (!preg_match('/^\d{9}$|^\d{13}$/', $isbn)) {
        echo json_encode([
            "status" => "error",
            "message" => "ISBN must be exactly 9 or 13 digits."
        ]);
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE books
        SET title=?, author=?, publisher=?, year_published=?, isbn=?, copies=?, available=?
        WHERE id=?");
    $stmt->bind_param("sssssiii", $title, $author, $publisher, $year, $isbn, $copies, $available, $id);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "✅ Book updated successfully!"];
    } else {
        $response = ["status" => "error", "message" => "❌ Error updating book: " . $stmt->error];
    }
    $stmt->close();

    echo json_encode($response);
    exit;
}

echo json_encode($response);
$mysqli->close();

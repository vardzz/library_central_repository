<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'librarian') {
    header('Location: index.php');
    exit;
}


require 'config.php'; // DB connection


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $copies = intval($_POST['copies'] ?? 1);


    if (!empty($title)) {
        $stmt = $mysqli->prepare("INSERT INTO books (title, author, publisher, year_published, isbn, copies, available) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisii", $title, $author, $publisher, $year, $isbn, $copies, $copies);


        if ($stmt->execute()) {
            $_SESSION['message'] = "Book added successfully: " . htmlspecialchars($title);
        } else {
            $_SESSION['message'] = "Error adding book: " . $mysqli->error;
        }


        $stmt->close();
    } else {
        $_SESSION['message'] = "Book title is required.";
    }


    // Redirect back to librarian dashboard after processing
    header('Location: librarian.php');
    exit;
}




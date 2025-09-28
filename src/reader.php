<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'reader') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">
        <h1>Reader Dashboard</h1>
        <div>
            <!-- Pass mode param so borrow-return.php knows what to show -->
            <a href="borrow-return.php?action=borrow"><button>Borrow</button></a>
            <a href="borrow-return.php?action=return"><button>Return</button></a>

            <a href="logout.php"><button>Logout</button></a>
        </div>
    </div>
    
    <div class="container">
        <?php include 'browse-view.php'; ?> <!-- Handles search, table, and JS -->
    </div>
</body>
</html>

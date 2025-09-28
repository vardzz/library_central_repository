<?php
session_start();
if (isset($_SESSION['role'])) {
    header('Location: ' . ($_SESSION['role'] == 'librarian' ? 'librarian.php' : 'reader.php'));
    exit;
}

if (isset($_POST['role']) && $_POST['role']) {
    $_SESSION['role'] = $_POST['role'];
    header('Location: ' . ($_POST['role'] == 'librarian' ? 'librarian.php' : 'reader.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Welcome</title>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="welcome-header">
        <h1>Library Management System</h1>
        <p>Your gateway to knowledge and stories</p>
    </div>
    <div class="container">
        <div class="welcome-card">
            <h2>Welcome! Please select your role to continue.</h2>
            <form method="POST" class="role-selection">
                <button type="submit" name="role" value="librarian">Librarian</button>
                <button type="submit" name="role" value="reader">Reader</button>
            </form>
        </div>
    </div>
</body>

</html>
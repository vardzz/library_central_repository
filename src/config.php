<?php
$servername = getenv('DB_HOST') ?: 'db';
$username   = getenv('DB_USER') ?: 'library_user';
$password   = getenv('DB_PASS') ?: 'lib_pass';
$dbname     = getenv('DB_NAME') ?: 'library';

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>

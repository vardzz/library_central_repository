<?php
// Load from environment variables
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'library';

$mysqli = new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

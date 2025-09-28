<?php
session_start();
session_destroy();  // Destroys all session data, including the role
header('Location: index.php');
exit;
?>
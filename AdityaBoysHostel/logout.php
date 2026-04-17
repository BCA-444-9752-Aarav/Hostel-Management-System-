<?php
require_once 'config/db.php';

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?>

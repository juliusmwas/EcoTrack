<?php
// logout.php - Ends user session and returns to homepage

session_start();

// Clear all session data
$_SESSION = [];

// Finally, destroy the session
session_destroy();

// Redirect to homepage
header("Location: index.php");
exit;

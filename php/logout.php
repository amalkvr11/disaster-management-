<?php
session_start();

// Remove all session variables
session_unset();

// Destroy the session
session_destroy();

// Prevent back button cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Redirect to homepage or login page
header("Location: /disaster-management-/html/index.html");
exit;
?>

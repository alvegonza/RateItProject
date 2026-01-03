<?php

// 1. SECURE SESSION STARTUP (Fixes the "Notice")
// Start the session ONLY if one is not already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. AUTHENTICATION CHECK
// If the 'user_id' session variable is NOT set, the user is not logged in.
if (!isset($_SESSION['user_id'])) {
    
    // Redirect the unauthorized user to the index/login page.
    header('Location: index.php');
    
    // CRITICAL: Stop script execution immediately after the redirect header is sent.
    exit(); 
}
?>
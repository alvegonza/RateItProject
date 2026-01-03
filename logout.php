<?php
// logout.php
session_start();

// 1. Clear all session variables
$_SESSION = [];

// 2. Destroy the session cookie (crucial for security)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session storage
session_destroy();

// Redirect to Landing Page
header("Location: index.php");
exit();
?>
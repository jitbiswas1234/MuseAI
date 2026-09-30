<?php
// logout.php
// Destroy session and log out user

require_once 'includes/functions.php';

// Empty the session array
$_SESSION = array();

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to index with success confirmation
session_start();
flash('general_success', 'You have been logged out successfully.', 'info');
redirect('index.php');
?>

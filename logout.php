<?php
// Logout endpoint for Nexa platform

session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie if it exists
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session data on the server
session_destroy();

// Redirect the user to the login page after logout
header('Location: login.php');
exit;

<?php
// Start session
session_start();

// Session timeout in minutes
$timeout = 30;

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Check user role
function hasRole($required_role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['role'] == $required_role || $_SESSION['role'] == 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not authorized
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: unauthorized.php');
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

// Logout function
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
<?php
// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Check if user has specific role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

// Check if user has any of the specified roles
function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['user_role'], $roles);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /modules/auth/login.php');
        exit;
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /modules/dashboard/index.php');
        exit;
    }
}

// Require any of specified roles
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        header('Location: /modules/dashboard/index.php');
        exit;
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Get current user name
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? null;
}

// Set user session
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['username'] = $user['username'];
}

// Clear user session
function clearUserSession() {
    session_unset();
    session_destroy();
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>

<?php
/**
 * ConnectMe - Authentication & Session Management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

// Configure session parameters securely
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', SESSION_NAME);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

/**
 * Check if current user is logged in
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

/**
 * Check if current logged in user is admin
 */
function isAdmin(): bool {
    return isLoggedIn() && !empty($_SESSION['is_admin']);
}

/**
 * Get current User ID
 */
function currentUserId(): ?int {
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Enforce logged-in authentication
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Please log in to access this page.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }

    // Security: Validate user active status from DB
    $db = getDB();
    $stmt = $db->prepare("SELECT is_active, is_admin FROM users WHERE id = ?");
    $stmt->execute([currentUserId()]);
    $user = $stmt->fetch();

    if (!$user || !$user['is_active']) {
        logoutUser();
        setFlashMessage('danger', 'Your account has been deactivated or suspended.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }

    // Refresh admin status in session
    $_SESSION['is_admin'] = (int)$user['is_admin'];
}

/**
 * Enforce Admin authorization
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        setFlashMessage('danger', 'Access Denied. Admin permissions required.');
        header('Location: ' . APP_URL . '/user/dashboard.php');
        exit;
    }
}

/**
 * Log in a user and initiate session
 */
function loginUser(int $userId, bool $isAdmin = false): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['is_admin'] = $isAdmin ? 1 : 0;
    $_SESSION['login_time'] = time();

    // Touch last updated timestamp in database
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$userId]);
}

/**
 * Log out user safely
 */
function logoutUser(): void {
    $_SESSION = [];
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
    session_destroy();
}

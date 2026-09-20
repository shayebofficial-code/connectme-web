<?php
/**
 * ConnectMe - CSRF Protection Utilities
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate or get existing CSRF Token
 */
function getCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate hidden CSRF Form Input
 */
function csrfInput(): string {
    $token = getCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate CSRF Token from POST request or Header
 */
function validateCSRFToken(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforce CSRF check or terminate execution
 */
function verifyCSRF(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validateCSRFToken()) {
            http_response_code(403);
            die("CSRF Token Validation Failed. Please refresh the page and try again.");
        }
    }
}

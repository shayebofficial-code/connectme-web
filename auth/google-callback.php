<?php
/**
 * ConnectMe - Google OAuth 2.0 Callback Processor
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/google.php';
require_once __DIR__ . '/../includes/auth.php';

$state = $_GET['state'] ?? '';
$code  = $_GET['code'] ?? '';

// Validate OAuth state
if (empty($state) || empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
    unset($_SESSION['oauth_state']);
    setFlashMessage('danger', 'Google authentication failed due to invalid state token.');
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}
unset($_SESSION['oauth_state']);

if (empty($code)) {
    setFlashMessage('danger', 'Google login cancelled or failed.');
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

// Exchange code for access token
$tokenData = getGoogleAccessToken($code);
if (!$tokenData || empty($tokenData['access_token'])) {
    setFlashMessage('danger', 'Failed to retrieve access token from Google.');
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

// Fetch Google User Profile
$googleUser = getGoogleUserInfo($tokenData['access_token']);
if (!$googleUser || empty($googleUser['sub']) || empty($googleUser['email'])) {
    setFlashMessage('danger', 'Failed to retrieve user profile from Google.');
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

$googleId = $googleUser['sub'];
$email    = filter_var($googleUser['email'], FILTER_VALIDATE_EMAIL);
$name     = $googleUser['name'] ?? 'ConnectMe User';
$picture  = $googleUser['picture'] ?? null;

if (!$email) {
    setFlashMessage('danger', 'Google account does not provide a valid email address.');
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

$db = getDB();

// 1. Check if Google ID exists
$stmt = $db->prepare("SELECT u.id, u.is_admin, u.is_active FROM users u WHERE u.google_id = ?");
$stmt->execute([$googleId]);
$user = $stmt->fetch();

if ($user) {
    if (!$user['is_active']) {
        setFlashMessage('danger', 'Your account has been deactivated or suspended.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }

    loginUser((int)$user['id'], (bool)$user['is_admin']);
    setFlashMessage('success', 'Logged in successfully with Google!');
    header('Location: ' . APP_URL . '/user/discover.php');
    exit;
}

// 2. Check if Email already exists locally (Link account)
$stmt = $db->prepare("SELECT u.id, u.is_admin, u.is_active FROM users u WHERE u.email = ?");
$stmt->execute([$email]);
$existingUser = $stmt->fetch();

if ($existingUser) {
    if (!$existingUser['is_active']) {
        setFlashMessage('danger', 'Your account has been deactivated or suspended.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }

    // Link Google ID to existing account
    $linkStmt = $db->prepare("UPDATE users SET google_id = ?, email_verified = 1 WHERE id = ?");
    $linkStmt->execute([$googleId, $existingUser['id']]);

    loginUser((int)$existingUser['id'], (bool)$existingUser['is_admin']);
    setFlashMessage('success', 'Google account successfully linked to your existing ConnectMe profile!');
    header('Location: ' . APP_URL . '/user/discover.php');
    exit;
}

// 3. Create new user account automatically
$db->beginTransaction();
try {
    $insertUser = $db->prepare("INSERT INTO users (email, google_id, email_verified) VALUES (?, ?, 1)");
    $insertUser->execute([$email, $googleId]);
    $newUserId = (int)$db->lastInsertId();

    // Default 18+ DOB for Google signups (user can adjust in profile)
    $defaultDob = date('Y-m-d', strtotime('-22 years'));
    $defaultAge = 22;

    $insertProfile = $db->prepare("
        INSERT INTO profiles (user_id, name, dob, age, gender, city, photo)
        VALUES (?, ?, ?, ?, 'female', 'Mumbai', 'default.jpg')
    ");
    $insertProfile->execute([$newUserId, $name, $defaultDob, $defaultAge]);

    $db->commit();

    loginUser($newUserId);
    setFlashMessage('success', 'Account created with Google! Please complete your profile details below.');
    header('Location: ' . APP_URL . '/user/profile.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    error_log("Google OAuth DB Registration Error: " . $e->getMessage());
    setFlashMessage('danger', 'An error occurred while setting up your account via Google.');
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

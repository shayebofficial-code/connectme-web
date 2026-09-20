<?php
/**
 * ConnectMe - API Endpoint: Like / Pass Profile & Mutual Match Trigger
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

verifyCSRF();

$fromUserId = currentUserId();
$toUserId   = (int)($_POST['to_user_id'] ?? 0);
$action     = trim($_POST['action'] ?? 'like'); // 'like' or 'pass'

if (!$toUserId || $toUserId === $fromUserId) {
    jsonResponse(['success' => false, 'error' => 'Invalid target user ID']);
}

$db = getDB();

if ($action === 'pass') {
    // Record pass action as a hidden flag or temporary record if desired, return success
    jsonResponse(['success' => true, 'is_match' => false]);
}

// 1. Insert Like Record (Ignore duplicate likes)
$likeStmt = $db->prepare("INSERT IGNORE INTO likes (from_user_id, to_user_id) VALUES (?, ?)");
$likeStmt->execute([$fromUserId, $toUserId]);

// 2. Check if reverse like exists (Mutual Match Trigger)
$checkReverse = $db->prepare("SELECT id FROM likes WHERE from_user_id = ? AND to_user_id = ?");
$checkReverse->execute([$toUserId, $fromUserId]);
$isMutual = (bool)$checkReverse->fetch();

$matchedUser = null;

if ($isMutual) {
    $u1 = min($fromUserId, $toUserId);
    $u2 = max($fromUserId, $toUserId);

    // Insert mutual match pair
    $matchStmt = $db->prepare("INSERT IGNORE INTO matches (user1_id, user2_id) VALUES (?, ?)");
    $matchStmt->execute([$u1, $u2]);

    $matchedUser = getUserProfileData($toUserId);
}

jsonResponse([
    'success'      => true,
    'is_match'     => $isMutual,
    'matched_user' => $matchedUser
]);

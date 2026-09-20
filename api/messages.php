<?php
/**
 * ConnectMe - API Endpoint: Messages & Polling
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
}

$userId = currentUserId();
$action = $_REQUEST['action'] ?? 'fetch';
$db = getDB();

if ($action === 'send') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
    }
    verifyCSRF();

    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $body       = trim($_POST['body'] ?? '');

    if (!$receiverId || empty($body)) {
        jsonResponse(['success' => false, 'error' => 'Message body and receiver ID are required']);
    }

    // Verify mutual match authorization
    $matchCheck = $db->prepare("
        SELECT id FROM matches 
        WHERE (user1_id = LEAST(:u1, :u2) AND user2_id = GREATEST(:u1, :u2))
    ");
    $matchCheck->execute(['u1' => $userId, 'u2' => $receiverId]);
    if (!$matchCheck->fetch()) {
        jsonResponse(['success' => false, 'error' => 'You can only message matched users'], 403);
    }

    // Insert Message (Escaping done on output)
    $stmt = $db->prepare("
        INSERT INTO messages (sender_id, receiver_id, body)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$userId, $receiverId, $body]);
    $msgId = $db->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => [
            'id'          => $msgId,
            'sender_id'   => $userId,
            'receiver_id' => $receiverId,
            'body'        => $body,
            'created_at'  => 'Just now'
        ]
    ]);
} 

elseif ($action === 'fetch') {
    $receiverId = (int)($_GET['receiver_id'] ?? 0);
    if (!$receiverId) {
        jsonResponse(['success' => false, 'error' => 'Receiver ID required']);
    }

    // Mark as read
    $db->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0")
       ->execute([$receiverId, $userId]);

    $stmt = $db->prepare("
        SELECT id, sender_id, receiver_id, body, created_at
        FROM messages
        WHERE (sender_id = :u1 AND receiver_id = :u2) OR (sender_id = :u2 AND receiver_id = :u1)
        ORDER BY created_at ASC
    ");
    $stmt->execute(['u1' => $userId, 'u2' => $receiverId]);
    $messages = $stmt->fetchAll();

    // Format timestamps
    foreach ($messages as &$m) {
        $m['created_at'] = timeAgo($m['created_at']);
    }

    jsonResponse(['success' => true, 'messages' => $messages]);
}

jsonResponse(['success' => false, 'error' => 'Invalid action']);

<?php
/**
 * ConnectMe - Real-Time Chat Conversation Screen
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$receiverId = (int)($_GET['match_id'] ?? 0);

if (!$receiverId) {
    setFlashMessage('warning', 'Invalid match selected.');
    header('Location: ' . APP_URL . '/user/matches.php');
    exit;
}

$db = getDB();

// Security: Verify mutual match exists between user & receiver
$stmt = $db->prepare("
    SELECT id FROM matches
    WHERE (user1_id = LEAST(:u1, :u2) AND user2_id = GREATEST(:u1, :u2))
");
$stmt->execute(['u1' => $userId, 'u2' => $receiverId]);

if (!$stmt->fetch()) {
    setFlashMessage('danger', 'You can only message users with whom you have a mutual match.');
    header('Location: ' . APP_URL . '/user/matches.php');
    exit;
}

// Check blocked status
$blockCheck = $db->prepare("
    SELECT id FROM blocks
    WHERE (blocker_id = :u1 AND blocked_id = :u2) OR (blocker_id = :u2 AND blocked_id = :u1)
");
$blockCheck->execute(['u1' => $userId, 'u2' => $receiverId]);
if ($blockCheck->fetch()) {
    setFlashMessage('danger', 'Messaging is unavailable because one of the users has blocked the other.');
    header('Location: ' . APP_URL . '/user/matches.php');
    exit;
}

// Fetch recipient profile data
$receiverProfile = getUserProfileData($receiverId);
if (!$receiverProfile) {
    setFlashMessage('danger', 'Match user profile not found.');
    header('Location: ' . APP_URL . '/user/matches.php');
    exit;
}

// Mark messages as read
$updateRead = $db->prepare("
    UPDATE messages SET is_read = 1
    WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
");
$updateRead->execute([$receiverId, $userId]);

// Fetch Chat History
$msgStmt = $db->prepare("
    SELECT id, sender_id, receiver_id, body, created_at
    FROM messages
    WHERE (sender_id = :u1 AND receiver_id = :u2) OR (sender_id = :u2 AND receiver_id = :u1)
    ORDER BY created_at ASC
");
$msgStmt->execute(['u1' => $userId, 'u2' => $receiverId]);
$messages = $msgStmt->fetchAll();
?>

<div style="max-width: 800px; margin: 20px auto; padding: 0 15px;">
    
    <!-- Chat Header -->
    <div class="glass-card" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0; display: flex; align-items: center; justify-content: space-between; padding: 16px 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="<?php echo APP_URL; ?>/user/matches.php" style="color: var(--text-secondary); font-size: 1.2rem; margin-right: 6px;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <img src="<?php echo APP_URL . '/uploads/profiles/' . e($receiverProfile['photo'] ?? 'default.jpg'); ?>" 
                 alt="Photo" 
                 style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($receiverProfile['name']); ?>&background=e11d48&color=fff';">
            <div>
                <h3 style="font-size: 1.1rem; margin-bottom: 2px;">
                    <?php echo e($receiverProfile['name']); ?>

                    <?php if ($receiverProfile['is_verified']): ?>
                        <span class="badge-verified"><i class="fa-solid fa-check"></i></span>
                    <?php endif; ?>
                </h3>
                <span style="font-size: 0.8rem; color: var(--success);"><i class="fa-solid fa-circle" style="font-size: 0.6rem;"></i> Connected Match</span>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="<?php echo APP_URL; ?>/user/report.php?reported_id=<?php echo $receiverId; ?>" class="btn-secondary-custom" style="padding: 6px 12px; font-size: 0.85rem;" title="Report User">
                <i class="fa-solid fa-flag"></i>
            </a>
            <a href="<?php echo APP_URL; ?>/user/block.php?block_id=<?php echo $receiverId; ?>" class="btn-secondary-custom" style="padding: 6px 12px; font-size: 0.85rem; color: var(--danger);" onclick="return confirm('Block this match?');" title="Block User">
                <i class="fa-solid fa-ban"></i>
            </a>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="messagesContainer" class="chat-messages-area" style="background: var(--surface); border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color); height: 440px;">
        <?php foreach ($messages as $msg): ?>
            <?php $isSent = ($msg['sender_id'] == $userId); ?>
            <div class="message-bubble <?php echo $isSent ? 'message-sent' : 'message-received'; ?>">
                <div class="message-text"><?php echo e($msg['body']); ?></div>
                <div class="message-time"><?php echo timeAgo($msg['created_at']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Message Input Bar -->
    <div class="glass-card" style="border-top-left-radius: 0; border-top-right-radius: 0; padding: 14px;">
        <form id="chatForm" style="display: flex; gap: 10px;">
            <input type="hidden" id="receiverId" value="<?php echo $receiverId; ?>">
            <input type="text" id="messageInput" class="form-control-custom" placeholder="Type a message..." required autocomplete="off">
            <button type="submit" class="btn-primary-custom" style="padding: 10px 20px;">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

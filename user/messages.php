<?php
/**
 * ConnectMe - Conversations Inbox List
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

// Query active conversation matches with last message snippet & unread count
$query = "
    SELECT p.user_id as match_user_id, p.name, p.photo, p.city, p.is_verified,
           (SELECT body FROM messages 
            WHERE (sender_id = :u AND receiver_id = p.user_id) OR (sender_id = p.user_id AND receiver_id = :u)
            ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages 
            WHERE (sender_id = :u AND receiver_id = p.user_id) OR (sender_id = p.user_id AND receiver_id = :u)
            ORDER BY created_at DESC LIMIT 1) as last_time,
           (SELECT COUNT(*) FROM messages 
            WHERE sender_id = p.user_id AND receiver_id = :u AND is_read = 0) as unread_count
    FROM matches m
    JOIN profiles p ON p.user_id = IF(m.user1_id = :u, m.user2_id, m.user1_id)
    WHERE m.user1_id = :u OR m.user2_id = :u
    ORDER BY last_time DESC, m.created_at DESC
";

$stmt = $db->prepare($query);
$stmt->execute(['u' => $userId]);
$conversations = $stmt->fetchAll();
?>

<div style="max-width: 760px; margin: 30px auto; padding: 0 15px;">
    <h2 style="margin-bottom: 8px;"><i class="fa-solid fa-comments" style="color: var(--primary);"></i> Conversations</h2>
    <p style="color: var(--text-secondary); margin-bottom: 24px;">
        Chat with your mutual matches.
    </p>

    <?php if (!empty($conversations)): ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($conversations as $conv): ?>
                <a href="<?php echo APP_URL; ?>/user/chat.php?match_id=<?php echo $conv['match_user_id']; ?>" class="glass-card" style="display: flex; align-items: center; justify-content: space-between; text-decoration: none; color: var(--text-primary); padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <img src="<?php echo APP_URL . '/uploads/profiles/' . e($conv['photo'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo e($conv['name']); ?>" 
                             style="width: 54px; height: 54px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($conv['name']); ?>&background=e11d48&color=fff';">
                        
                        <div>
                            <div style="font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 6px;">
                                <?php echo e($conv['name']); ?>

                                <?php if ($conv['is_verified']): ?>
                                    <span class="badge-verified"><i class="fa-solid fa-check"></i></span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo e($conv['last_message'] ?? 'Click to start conversation...'); ?>

                            </div>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <?php if (!empty($conv['last_time'])): ?>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">
                                <?php echo timeAgo($conv['last_time']); ?>

                            </div>
                        <?php endif; ?>
                        
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span style="background: var(--primary); color: white; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 999px;">
                                <?php echo $conv['unread_count']; ?> new
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 50px 20px;">
            <i class="fa-solid fa-comment-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 16px;"></i>
            <h3 style="margin-bottom: 8px;">No Active Conversations</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">Match with users on Discover to open chat threads!</p>
            <a href="<?php echo APP_URL; ?>/user/discover.php" class="btn-primary-custom">Discover People</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

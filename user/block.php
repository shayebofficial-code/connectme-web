<?php
/**
 * ConnectMe - Block User & Blocked List Controller
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

$blockId = (int)($_GET['block_id'] ?? 0);
$unblockId = (int)($_GET['unblock_id'] ?? 0);
$action = $_GET['action'] ?? '';

// Process Block Action
if ($blockId && $blockId !== $userId) {
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO blocks (blocker_id, blocked_id) VALUES (?, ?)");
        $stmt->execute([$userId, $blockId]);

        // Remove mutual match if exists
        $delMatch = $db->prepare("
            DELETE FROM matches
            WHERE (user1_id = LEAST(:u1, :u2) AND user2_id = GREATEST(:u1, :u2))
        ");
        $delMatch->execute(['u1' => $userId, 'u2' => $blockId]);

        setFlashMessage('success', 'User has been blocked successfully.');
    } catch (Exception $e) {
        error_log("Block Error: " . $e->getMessage());
    }
    header('Location: ' . APP_URL . '/user/discover.php');
    exit;
}

// Process Unblock Action
if ($unblockId) {
    $delBlock = $db->prepare("DELETE FROM blocks WHERE blocker_id = ? AND blocked_id = ?");
    $delBlock->execute([$userId, $unblockId]);
    setFlashMessage('success', 'User unblocked.');
    header('Location: ' . APP_URL . '/user/block.php?action=view_blocked');
    exit;
}

// View Blocked List View
$stmt = $db->prepare("
    SELECT b.created_at as blocked_at, p.*, u.id as blocked_user_id
    FROM blocks b
    JOIN users u ON b.blocked_id = u.id
    JOIN profiles p ON p.user_id = u.id
    WHERE b.blocker_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$userId]);
$blockedUsers = $stmt->fetchAll();
?>

<div style="max-width: 700px; margin: 30px auto; padding: 0 15px;">
    <h2 style="margin-bottom: 8px;"><i class="fa-solid fa-user-slash" style="color: var(--danger);"></i> Blocked Users</h2>
    <p style="color: var(--text-secondary); margin-bottom: 24px;">
        Blocked users cannot see your profile, send messages, or appear in recommendations.
    </p>

    <?php if (!empty($blockedUsers)): ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($blockedUsers as $blocked): ?>
                <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between; padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="<?php echo APP_URL . '/uploads/profiles/' . e($blocked['photo'] ?? 'default.jpg'); ?>" 
                             alt="Photo" 
                             style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);"
                             onerror="this.src='https://ui-avatars.com/api/?name=User&background=e11d48&color=fff';">
                        <div>
                            <div style="font-weight: 700;"><?php echo e($blocked['name']); ?>, <?php echo e($blocked['age']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo e($blocked['city']); ?> • Blocked <?php echo timeAgo($blocked['blocked_at']); ?></div>
                        </div>
                    </div>

                    <a href="<?php echo APP_URL; ?>/user/block.php?unblock_id=<?php echo $blocked['blocked_user_id']; ?>" class="btn-secondary-custom" style="padding: 6px 14px; font-size: 0.85rem;">
                        Unblock
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 40px 20px;">
            <p style="color: var(--text-secondary);">You have not blocked any users.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

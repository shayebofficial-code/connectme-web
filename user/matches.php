<?php
/**
 * ConnectMe - Mutual Matches Page
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

// Fetch all mutual matches for current user
$query = "
    SELECT m.id as match_id, m.created_at as matched_at,
           p.user_id as matched_user_id, p.name, p.age, p.city, p.photo, p.is_verified, p.is_premium
    FROM matches m
    JOIN profiles p ON p.user_id = IF(m.user1_id = :current_user, m.user2_id, m.user1_id)
    WHERE m.user1_id = :current_user OR m.user2_id = :current_user
    ORDER BY m.created_at DESC
";

$stmt = $db->prepare($query);
$stmt->execute(['current_user' => $userId]);
$matches = $stmt->fetchAll();
?>

<div style="max-width: 900px; margin: 30px auto; padding: 0 15px;">
    <h2 style="margin-bottom: 8px;"><i class="fa-solid fa-comments" style="color: var(--primary);"></i> Your Matches</h2>
    <p style="color: var(--text-secondary); margin-bottom: 24px; font-size: 0.95rem;">
        These users have mutually liked your profile! Start a conversation anytime.
    </p>

    <?php if (!empty($matches)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
            <?php foreach ($matches as $match): ?>
                <div class="glass-card" style="display: flex; flex-direction: column; align-items: center; text-align: center; padding: 20px;">
                    <img src="<?php echo APP_URL . '/uploads/profiles/' . e($match['photo'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo e($match['name']); ?>" 
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); margin-bottom: 12px;"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($match['name']); ?>&background=e11d48&color=fff';">

                    <h3 style="font-size: 1.2rem; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                        <?php echo e($match['name']); ?>, <?php echo e($match['age']); ?>

                        <?php if ($match['is_verified']): ?>
                            <span class="badge-verified" style="font-size: 0.65rem;"><i class="fa-solid fa-check"></i></span>
                        <?php endif; ?>
                    </h3>

                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 12px;">
                        <i class="fa-solid fa-location-dot"></i> <?php echo e($match['city']); ?> • Matched <?php echo timeAgo($match['matched_at']); ?>

                    </p>

                    <a href="<?php echo APP_URL; ?>/user/chat.php?match_id=<?php echo $match['matched_user_id']; ?>" class="btn-primary-custom" style="width: 100%; padding: 10px; font-size: 0.9rem;">
                        <i class="fa-solid fa-paper-plane"></i> Chat Now
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 50px 20px;">
            <i class="fa-solid fa-heart-crack" style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 16px;"></i>
            <h3 style="font-size: 1.4rem; margin-bottom: 8px;">No Matches Yet</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                Keep swiping on the Discover page to get your first mutual match!
            </p>
            <a href="<?php echo APP_URL; ?>/user/discover.php" class="btn-primary-custom">
                <i class="fa-solid fa-fire"></i> Go to Discover
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

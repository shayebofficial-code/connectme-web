<?php
/**
 * ConnectMe - Likes Hub (Received & Sent Likes)
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

$tab = $_GET['tab'] ?? 'received'; // 'received' or 'sent'
$isPremium = checkUserPremiumStatus($userId);

// Fetch Likes Received (Users who liked current user)
$receivedStmt = $db->prepare("
    SELECT l.created_at as liked_at, p.*, u.id as liker_user_id
    FROM likes l
    JOIN users u ON l.from_user_id = u.id
    JOIN profiles p ON p.user_id = u.id
    WHERE l.to_user_id = ?
    ORDER BY l.created_at DESC
");
$receivedStmt->execute([$userId]);
$receivedLikes = $receivedStmt->fetchAll();

// Fetch Likes Sent (Users current user liked)
$sentStmt = $db->prepare("
    SELECT l.created_at as liked_at, p.*, u.id as target_user_id
    FROM likes l
    JOIN users u ON l.to_user_id = u.id
    JOIN profiles p ON p.user_id = u.id
    WHERE l.from_user_id = ?
    ORDER BY l.created_at DESC
");
$sentStmt->execute([$userId]);
$sentLikes = $sentStmt->fetchAll();
?>

<div style="max-width: 900px; margin: 30px auto; padding: 0 15px;">
    <!-- Tab Switcher -->
    <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 24px;">
        <a href="<?php echo APP_URL; ?>/user/likes.php?tab=received" 
           class="<?php echo $tab === 'received' ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>">
            <i class="fa-solid fa-heart"></i> Liked You (<?php echo count($receivedLikes); ?>)
        </a>
        <a href="<?php echo APP_URL; ?>/user/likes.php?tab=sent" 
           class="<?php echo $tab === 'sent' ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>">
            <i class="fa-solid fa-paper-plane"></i> You Liked (<?php echo count($sentLikes); ?>)
        </a>
    </div>

    <?php if ($tab === 'received'): ?>
        <h3 style="margin-bottom: 16px;">People Who Liked Your Profile</h3>

        <?php if (!$isPremium && !empty($receivedLikes)): ?>
            <!-- Premium Locked Preview for Non-Premium Users -->
            <div class="glass-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(225, 29, 72, 0.2)); border-color: var(--gold); text-align: center; padding: 30px; margin-bottom: 20px;">
                <i class="fa-solid fa-lock" style="font-size: 2.5rem; color: var(--gold); margin-bottom: 12px;"></i>
                <h3 style="color: var(--gold); margin-bottom: 8px;">Unlock "Who Liked You"</h3>
                <p style="color: var(--text-primary); margin-bottom: 20px;">
                    <?php echo count($receivedLikes); ?> people have liked your profile! Upgrade to Premium to reveal their photos and match instantly.
                </p>
                <a href="<?php echo APP_URL; ?>/user/subscribe.php" class="btn-primary-custom" style="background: linear-gradient(135deg, var(--gold), #d97706); color: white;">
                    <i class="fa-solid fa-crown"></i> Get Premium Access
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($receivedLikes)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px;">
                <?php foreach ($receivedLikes as $like): ?>
                    <div class="glass-card" style="text-align: center; position: relative;">
                        <img src="<?php echo APP_URL . '/uploads/profiles/' . e($like['photo'] ?? 'default.jpg'); ?>" 
                             alt="Liker" 
                             style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); margin-bottom: 10px; <?php echo !$isPremium ? 'filter: blur(8px);' : ''; ?>"
                             onerror="this.src='https://ui-avatars.com/api/?name=User&background=e11d48&color=fff';">

                        <h4 style="font-size: 1.1rem; margin-bottom: 4px;">
                            <?php echo $isPremium ? e($like['name']) . ', ' . e($like['age']) : 'Secret Admirer'; ?>

                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem; margin-bottom: 12px;">
                            <?php echo $isPremium ? e($like['city']) : 'Location hidden'; ?> • <?php echo timeAgo($like['liked_at']); ?>

                        </p>

                        <?php if ($isPremium): ?>
                            <button onclick="sendProfileReaction(<?php echo $like['liker_user_id']; ?>, 'like')" class="btn-primary-custom" style="width: 100%; padding: 8px; font-size: 0.85rem;">
                                ❤️ Like Back & Match
                            </button>
                        <?php else: ?>
                            <a href="<?php echo APP_URL; ?>/user/subscribe.php" class="btn-secondary-custom" style="width: 100%; padding: 8px; font-size: 0.85rem; color: var(--gold);">
                                <i class="fa-solid fa-lock"></i> Unlock
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align: center; padding: 40px 20px;">
                <p style="color: var(--text-secondary);">No likes received yet. Try updating your profile photos to attract more attention!</p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <h3 style="margin-bottom: 16px;">Profiles You Have Liked</h3>
        <?php if (!empty($sentLikes)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px;">
                <?php foreach ($sentLikes as $like): ?>
                    <div class="glass-card" style="text-align: center;">
                        <img src="<?php echo APP_URL . '/uploads/profiles/' . e($like['photo'] ?? 'default.jpg'); ?>" 
                             alt="User" 
                             style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 2px solid var(--secondary); margin-bottom: 10px;"
                             onerror="this.src='https://ui-avatars.com/api/?name=User&background=e11d48&color=fff';">

                        <h4 style="font-size: 1.1rem; margin-bottom: 4px;"><?php echo e($like['name']); ?>, <?php echo e($like['age']); ?></h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem;"><i class="fa-solid fa-location-dot"></i> <?php echo e($like['city']); ?></p>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Liked <?php echo timeAgo($like['liked_at']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align: center; padding: 40px 20px;">
                <p style="color: var(--text-secondary);">You haven't liked any profiles yet.</p>
                <a href="<?php echo APP_URL; ?>/user/discover.php" class="btn-primary-custom" style="margin-top: 12px;">Discover People</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

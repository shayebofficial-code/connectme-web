<?php
/**
 * ConnectMe - User Dashboard
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

// Fetch Profile Data
$profile = getUserProfileData($userId);
$isPremium = checkUserPremiumStatus($userId);

// Fetch Total Matches Count
$stmt = $db->prepare("
    SELECT COUNT(*) FROM matches
    WHERE user1_id = ? OR user2_id = ?
");
$stmt->execute([$userId, $userId]);
$matchesCount = (int)$stmt->fetchColumn();

// Fetch Likes Received Count
$stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE to_user_id = ?");
$stmt->execute([$userId]);
$likesReceivedCount = (int)$stmt->fetchColumn();

// Fetch Likes Sent Count
$stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE from_user_id = ?");
$stmt->execute([$userId]);
$likesSentCount = (int)$stmt->fetchColumn();
?>

<div style="max-width: 1000px; margin: 30px auto; padding: 0 15px;">
    <!-- Welcome Header Card -->
    <div class="glass-card" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <img src="<?php echo APP_URL . '/uploads/profiles/' . e($profile['photo'] ?? 'default.jpg'); ?>" 
                 alt="Profile Photo"
                 style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary);"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($profile['name'] ?? 'User'); ?>&background=e11d48&color=fff';">
            <div>
                <h2 style="font-size: 1.6rem; display: flex; align-items: center; gap: 8px;">
                    Hi, <?php echo e($profile['name'] ?? 'User'); ?>!
                    <?php if ($profile['is_verified']): ?>
                        <span class="badge-verified"><i class="fa-solid fa-check"></i> Verified</span>
                    <?php endif; ?>
                    <?php if ($isPremium): ?>
                        <span class="badge-premium"><i class="fa-solid fa-crown"></i> Premium</span>
                    <?php endif; ?>
                </h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> <?php echo e($profile['city'] ?? 'Location not set'); ?> • <?php echo e($profile['age'] ?? ''); ?> yrs
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="<?php echo APP_URL; ?>/user/discover.php" class="btn-primary-custom">
                <i class="fa-solid fa-fire"></i> Start Swiping
            </a>
            <a href="<?php echo APP_URL; ?>/user/profile.php" class="btn-secondary-custom">
                <i class="fa-solid fa-pen-to-square"></i> Edit Profile
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <a href="<?php echo APP_URL; ?>/user/matches.php" class="glass-card" style="text-align: center; text-decoration: none;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary);"><?php echo $matchesCount; ?></div>
            <div style="color: var(--text-secondary); font-weight: 600;"><i class="fa-solid fa-comments"></i> Mutual Matches</div>
        </a>

        <a href="<?php echo APP_URL; ?>/user/likes.php" class="glass-card" style="text-align: center; text-decoration: none;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--gold);"><?php echo $likesReceivedCount; ?></div>
            <div style="color: var(--text-secondary); font-weight: 600;"><i class="fa-solid fa-heart"></i> Liked You</div>
        </a>

        <a href="<?php echo APP_URL; ?>/user/likes.php?tab=sent" class="glass-card" style="text-align: center; text-decoration: none;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--secondary);"><?php echo $likesSentCount; ?></div>
            <div style="color: var(--text-secondary); font-weight: 600;"><i class="fa-solid fa-paper-plane"></i> Profiles You Liked</div>
        </a>
    </div>

    <!-- Premium Upgrade Banner if not premium -->
    <?php if (!$isPremium): ?>
        <div class="glass-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(225, 29, 72, 0.2)); border-color: var(--gold); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="color: var(--gold); margin-bottom: 4px;"><i class="fa-solid fa-crown"></i> Upgrade to ConnectMe Premium</h3>
                <p style="color: var(--text-primary); font-size: 0.95rem;">Unlock unlimited likes, see who liked your profile, and chat with priority placement!</p>
            </div>
            <a href="<?php echo APP_URL; ?>/user/subscribe.php" class="btn-primary-custom" style="background: linear-gradient(135deg, var(--gold), #d97706); color: white;">
                View Plans (From ₹199)
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

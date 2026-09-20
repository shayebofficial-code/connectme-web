<?php
/**
 * ConnectMe - Discover People Page (Swipeable Profile Cards)
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

// Filter inputs
$filterGender   = trim($_GET['gender'] ?? 'everyone');
$filterCity     = trim($_GET['city'] ?? '');
$filterMinAge   = (int)($_GET['min_age'] ?? 18);
$filterMaxAge   = (int)($_GET['max_age'] ?? 60);
$filterInterest = trim($_GET['interest'] ?? '');

// Build Discover SQL query excluding self, liked users, blocked/blocker users
$query = "
    SELECT p.*, u.id as target_user_id
    FROM profiles p
    JOIN users u ON p.user_id = u.id
    WHERE u.id != :current_user
      AND u.is_active = 1
      -- Exclude users already liked by current user
      AND u.id NOT IN (SELECT to_user_id FROM likes WHERE from_user_id = :current_user)
      -- Exclude blocked users (both blocker & blocked)
      AND u.id NOT IN (SELECT blocked_id FROM blocks WHERE blocker_id = :current_user)
      AND u.id NOT IN (SELECT blocker_id FROM blocks WHERE blocked_id = :current_user)
";

$params = ['current_user' => $userId];

if ($filterGender !== 'everyone' && in_array($filterGender, ['male', 'female', 'non-binary', 'other'])) {
    $query .= " AND p.gender = :gender";
    $params['gender'] = $filterGender;
}

if (!empty($filterCity)) {
    $query .= " AND p.city LIKE :city";
    $params['city'] = '%' . $filterCity . '%';
}

if ($filterMinAge > 18) {
    $query .= " AND p.age >= :min_age";
    $params['min_age'] = $filterMinAge;
}

if ($filterMaxAge < 80) {
    $query .= " AND p.age <= :max_age";
    $params['max_age'] = $filterMaxAge;
}

if (!empty($filterInterest)) {
    $query .= " AND p.interests LIKE :interest";
    $params['interest'] = '%' . $filterInterest . '%';
}

$query .= " ORDER BY p.is_premium DESC, RAND() LIMIT 1";

$stmt = $db->prepare($query);
$stmt->execute($params);
$candidate = $stmt->fetch();
?>

<div style="max-width: 1000px; margin: 20px auto; padding: 0 15px;">
    
    <!-- Search / Filter Accordion Header -->
    <div class="glass-card" style="margin-bottom: 20px;">
        <form method="GET" action="<?php echo APP_URL; ?>/user/discover.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; align-items: end;">
            <div>
                <label class="form-label-custom">Gender</label>
                <select name="gender" class="form-control-custom">
                    <option value="everyone" <?php echo $filterGender === 'everyone' ? 'selected' : ''; ?>>Everyone</option>
                    <option value="female" <?php echo $filterGender === 'female' ? 'selected' : ''; ?>>Female</option>
                    <option value="male" <?php echo $filterGender === 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="non-binary" <?php echo $filterGender === 'non-binary' ? 'selected' : ''; ?>>Non-Binary</option>
                </select>
            </div>

            <div>
                <label class="form-label-custom">City</label>
                <input type="text" name="city" class="form-control-custom" value="<?php echo e($filterCity); ?>" placeholder="e.g. Mumbai">
            </div>

            <div>
                <label class="form-label-custom">Min Age</label>
                <input type="number" name="min_age" class="form-control-custom" value="<?php echo $filterMinAge; ?>" min="18" max="80">
            </div>

            <div>
                <label class="form-label-custom">Max Age</label>
                <input type="number" name="max_age" class="form-control-custom" value="<?php echo $filterMaxAge; ?>" min="18" max="80">
            </div>

            <div>
                <button type="submit" class="btn-secondary-custom" style="width: 100%;">
                    <i class="fa-solid fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Discover Card Area -->
    <div class="discover-container">
        <?php if ($candidate): ?>
            <div class="profile-card">
                <img src="<?php echo APP_URL . '/uploads/profiles/' . e($candidate['photo'] ?? 'default.jpg'); ?>" 
                     alt="<?php echo e($candidate['name']); ?>" 
                     class="profile-card-image"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($candidate['name']); ?>&background=e11d48&color=fff';">

                <div class="profile-card-overlay">
                    <div class="profile-name-age">
                        <?php echo e($candidate['name']); ?>, <?php echo e($candidate['age']); ?>

                        <?php if ($candidate['is_verified']): ?>
                            <span class="badge-verified"><i class="fa-solid fa-check"></i> Verified</span>
                        <?php endif; ?>
                        <?php if ($candidate['is_premium']): ?>
                            <span class="badge-premium"><i class="fa-solid fa-crown"></i> VIP</span>
                        <?php endif; ?>
                    </div>

                    <div class="profile-city">
                        <i class="fa-solid fa-location-dot"></i> <?php echo e($candidate['city']); ?>

                    </div>

                    <?php if (!empty($candidate['bio'])): ?>
                        <div class="profile-bio">
                            "<?php echo e($candidate['bio']); ?>"
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($candidate['interests'])): ?>
                        <div class="tags-container">
                            <?php 
                            $tags = array_map('trim', explode(',', $candidate['interests']));
                            foreach ($tags as $tag): 
                                if (!empty($tag)):
                            ?>
                                <span class="tag-item">#<?php echo e($tag); ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="card-actions">
                        <button onclick="sendProfileReaction(<?php echo $candidate['target_user_id']; ?>, 'pass')" class="action-btn action-btn-pass" title="Pass">
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                        <button onclick="sendProfileReaction(<?php echo $candidate['target_user_id']; ?>, 'like')" class="action-btn action-btn-like" title="Like Profile">
                            <i class="fa-solid fa-heart"></i>
                        </button>

                        <a href="<?php echo APP_URL; ?>/user/report.php?reported_id=<?php echo $candidate['target_user_id']; ?>" class="action-btn action-btn-block" title="Report User">
                            <i class="fa-solid fa-flag"></i>
                        </a>

                        <a href="<?php echo APP_URL; ?>/user/block.php?block_id=<?php echo $candidate['target_user_id']; ?>" class="action-btn action-btn-block" onclick="return confirm('Block this user? They will no longer be visible.');" title="Block User">
                            <i class="fa-solid fa-ban"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align: center; padding: 50px 20px;">
                <i class="fa-solid fa-compass" style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 16px;"></i>
                <h3 style="font-size: 1.5rem; margin-bottom: 10px;">No More Profiles Nearby</h3>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">
                    You've reviewed all profiles matching your current filters! Try broadening your search filters or check back later.
                </p>
                <a href="<?php echo APP_URL; ?>/user/discover.php" class="btn-primary-custom">
                    <i class="fa-solid fa-rotate"></i> Reset Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function loadNextDiscoverCard() {
    window.location.reload();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

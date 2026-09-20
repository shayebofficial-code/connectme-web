<?php
/**
 * ConnectMe - Admin Profile & Photo Moderation
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $profileId = (int)($_POST['profile_id'] ?? 0);
    $action    = $_POST['action'] ?? '';

    if ($profileId) {
        if ($action === 'toggle_verify') {
            $db->prepare("UPDATE profiles SET is_verified = NOT is_verified WHERE id = ?")->execute([$profileId]);
            setFlashMessage('success', 'Profile verification badge updated.');
        } elseif ($action === 'reset_photo') {
            $db->prepare("UPDATE profiles SET photo = 'default.jpg' WHERE id = ?")->execute([$profileId]);
            setFlashMessage('warning', 'Profile photo reset to default.');
        }
    }
    header('Location: ' . APP_URL . '/admin/profiles.php');
    exit;
}

$stmt = $db->query("
    SELECT p.*, u.email
    FROM profiles p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.updated_at DESC LIMIT 40
");
$profiles = $stmt->fetchAll();
?>

<div style="max-width: 1100px; margin: 30px auto; padding: 0 15px;">
    <h2><i class="fa-solid fa-image" style="color: var(--secondary);"></i> Profile Photo Moderation & Verification</h2>
    <p style="color: var(--text-secondary); margin-bottom: 24px;">
        Review uploaded photos and grant verification badges.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px;">
        <?php foreach ($profiles as $p): ?>
            <div class="glass-card" style="text-align: center; padding: 16px;">
                <img src="<?php echo APP_URL . '/uploads/profiles/' . e($p['photo']); ?>" 
                     alt="Photo" 
                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border-color); margin-bottom: 10px;"
                     onerror="this.src='https://ui-avatars.com/api/?name=User&background=e11d48&color=fff';">

                <h4 style="font-size: 1.05rem; margin-bottom: 4px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                    <?php echo e($p['name']); ?>, <?php echo e($p['age']); ?>

                    <?php if ($p['is_verified']): ?>
                        <span class="badge-verified" style="font-size: 0.65rem;"><i class="fa-solid fa-check"></i></span>
                    <?php endif; ?>
                </h4>

                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;"><?php echo e($p['city']); ?></p>

                <form method="POST" style="display: flex; flex-direction: column; gap: 6px;">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="profile_id" value="<?php echo $p['id']; ?>">

                    <button type="submit" name="action" value="toggle_verify" class="btn-secondary-custom" style="padding: 6px; font-size: 0.8rem; <?php echo $p['is_verified'] ? 'color: var(--success);' : ''; ?>">
                        <i class="fa-solid fa-certificate"></i> <?php echo $p['is_verified'] ? 'Remove Badge' : 'Verify Profile'; ?>

                    </button>

                    <button type="submit" name="action" value="reset_photo" class="btn-secondary-custom" style="padding: 6px; font-size: 0.8rem; color: var(--danger);" onclick="return confirm('Reset photo for inappropriate content?');">
                        <i class="fa-solid fa-trash"></i> Reset Photo
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

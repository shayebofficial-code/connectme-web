<?php
/**
 * ConnectMe - User Account Settings
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    // Password change process
    if (!empty($newPass)) {
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user['password_hash'] && !password_verify($currentPass, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'New password and confirmation do not match.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $updatePass = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updatePass->execute([$newHash, $userId]);
            setFlashMessage('success', 'Your password has been changed successfully.');
            header('Location: ' . APP_URL . '/user/settings.php');
            exit;
        }
    }
}
?>

<div style="max-width: 560px; margin: 30px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="margin-bottom: 8px;"><i class="fa-solid fa-gear" style="color: var(--primary);"></i> Account Settings</h2>
        <p style="color: var(--text-secondary); margin-bottom: 24px; font-size: 0.95rem;">
            Manage security and account preferences.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo APP_URL; ?>/user/settings.php" method="POST">
            <?php echo csrfInput(); ?>

            <h4 style="margin-bottom: 14px; color: var(--text-primary);">Change Password</h4>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">Current Password</label>
                <input type="password" name="current_password" class="form-control-custom" placeholder="••••••••">
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">New Password (Min. 8 chars)</label>
                <input type="password" name="new_password" class="form-control-custom" placeholder="••••••••">
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label-custom">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control-custom" placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary-custom" style="width: 100%;">
                <i class="fa-solid fa-shield-halved"></i> Update Password
            </button>
        </form>

        <hr style="margin: 30px 0; border-color: var(--border-color);">

        <h4 style="color: var(--danger); margin-bottom: 8px;">Danger Zone</h4>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 14px;">
            Deactivating your profile hides your account from Discover and deletes active chats.
        </p>
        <a href="<?php echo APP_URL; ?>/user/block.php?action=view_blocked" class="btn-secondary-custom" style="width: 100%;">
            <i class="fa-solid fa-user-slash"></i> View Blocked Users List
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

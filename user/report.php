<?php
/**
 * ConnectMe - Report User Form
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$reportedId = (int)($_GET['reported_id'] ?? $_POST['reported_id'] ?? 0);

if (!$reportedId || $reportedId === $userId) {
    setFlashMessage('warning', 'Invalid user selected for report.');
    header('Location: ' . APP_URL . '/user/discover.php');
    exit;
}

$db = getDB();
$reportedProfile = getUserProfileData($reportedId);
if (!$reportedProfile) {
    setFlashMessage('danger', 'Reported user not found.');
    header('Location: ' . APP_URL . '/user/discover.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $reason      = trim($_POST['reason'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($reason)) {
        $errors[] = 'Please select a reason for reporting this profile.';
    } else {
        $stmt = $db->prepare("
            INSERT INTO reports (reporter_id, reported_id, reason, description, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$userId, $reportedId, $reason, $description]);

        setFlashMessage('success', 'Thank you. Your report has been submitted for admin review.');
        header('Location: ' . APP_URL . '/user/discover.php');
        exit;
    }
}
?>

<div style="max-width: 520px; margin: 30px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="color: var(--danger); margin-bottom: 8px;"><i class="fa-solid fa-flag"></i> Report User Profile</h2>
        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.95rem;">
            Help keep ConnectMe safe. Reporting is 100% anonymous and confidential.
        </p>

        <!-- Profile snippet -->
        <div style="display: flex; align-items: center; gap: 12px; background: var(--surface-light); padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px;">
            <img src="<?php echo APP_URL . '/uploads/profiles/' . e($reportedProfile['photo'] ?? 'default.jpg'); ?>" 
                 alt="Reported" 
                 style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;"
                 onerror="this.src='https://ui-avatars.com/api/?name=User&background=e11d48&color=fff';">
            <div>
                <strong>Reporting: <?php echo e($reportedProfile['name']); ?>, <?php echo e($reportedProfile['age']); ?></strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo e($reportedProfile['city']); ?></div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>

        <form action="<?php echo APP_URL; ?>/user/report.php" method="POST">
            <?php echo csrfInput(); ?>
            <input type="hidden" name="reported_id" value="<?php echo $reportedId; ?>">

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">Reason for Report</label>
                <select name="reason" class="form-control-custom" required>
                    <option value="">Select a reason...</option>
                    <option value="Fake Profile / Impersonation">Fake Profile / Impersonation</option>
                    <option value="Asking for Money / OTP / Scam">Asking for Money / OTP / Financial Scam</option>
                    <option value="Harassment / Offensive Behavior">Harassment / Offensive Behavior</option>
                    <option value="Inappropriate Photos / Nudity">Inappropriate Photos / Nudity</option>
                    <option value="Underage User (Under 18)">Underage User (Under 18)</option>
                    <option value="Spam / Commercial Advertising">Spam / Commercial Advertising</option>
                    <option value="Other Misconduct">Other Misconduct</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label-custom">Additional Details (Optional)</label>
                <textarea name="description" class="form-control-custom" rows="3" placeholder="Provide any extra details or context to assist moderation..."></textarea>
            </div>

            <button type="submit" class="btn-primary-custom" style="width: 100%; background: linear-gradient(135deg, var(--danger), #be123c);">
                <i class="fa-solid fa-paper-plane"></i> Submit Confidential Report
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

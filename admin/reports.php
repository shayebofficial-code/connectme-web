<?php
/**
 * ConnectMe - Admin Reports Review Controller
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $reportId = (int)($_POST['report_id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    if ($reportId) {
        $repStmt = $db->prepare("SELECT reported_id FROM reports WHERE id = ?");
        $repStmt->execute([$reportId]);
        $report = $repStmt->fetch();

        if ($action === 'dismiss') {
            $db->prepare("UPDATE reports SET status = 'dismissed' WHERE id = ?")->execute([$reportId]);
            setFlashMessage('info', 'Report dismissed.');
        } elseif ($action === 'ban_user' && $report) {
            $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$report['reported_id']]);
            $db->prepare("UPDATE reports SET status = 'actioned' WHERE id = ?")->execute([$reportId]);
            setFlashMessage('success', 'Reported user has been deactivated and report actioned.');
        }
    }
    header('Location: ' . APP_URL . '/admin/reports.php');
    exit;
}

$query = "
    SELECT r.*, 
           rep.name as reporter_name, 
           target.name as reported_name, target_u.id as reported_user_id, target_u.is_active as reported_is_active
    FROM reports r
    JOIN profiles rep ON r.reporter_id = rep.user_id
    JOIN profiles target ON r.reported_id = target.user_id
    JOIN users target_u ON target.user_id = target_u.id
    ORDER BY r.status ASC, r.created_at DESC
";

$stmt = $db->query($query);
$reports = $stmt->fetchAll();
?>

<div style="max-width: 1000px; margin: 30px auto; padding: 0 15px;">
    <h2><i class="fa-solid fa-flag" style="color: var(--danger);"></i> User Misconduct Reports</h2>
    <p style="color: var(--text-secondary); margin-bottom: 24px;">
        Review reports submitted by users for safety violations, scams, or harassment.
    </p>

    <?php if (!empty($reports)): ?>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($reports as $rep): ?>
                <div class="glass-card" style="border-left: 4px solid <?php echo $rep['status'] === 'pending' ? 'var(--danger)' : 'var(--border-color)'; ?>; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 0.8rem; background: var(--surface-light); color: var(--text-secondary); padding: 2px 8px; border-radius: 4px;">
                                Status: <strong><?php echo strtoupper(e($rep['status'])); ?></strong>
                            </span>
                            <h3 style="margin-top: 6px; font-size: 1.15rem; color: var(--danger);">
                                Reason: <?php echo e($rep['reason']); ?>

                            </h3>
                        </div>

                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            Reported <?php echo timeAgo($rep['created_at']); ?>

                        </div>
                    </div>

                    <p style="color: var(--text-primary); font-size: 0.95rem; margin-bottom: 16px; background: rgba(15, 23, 42, 0.4); padding: 12px; border-radius: var(--radius-sm);">
                        <?php echo e($rep['description'] ?? 'No detailed description provided.'); ?>

                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; font-size: 0.85rem; color: var(--text-secondary);">
                        <div>
                            <strong>Reporter:</strong> <?php echo e($rep['reporter_name']); ?> | 
                            <strong>Reported:</strong> <?php echo e($rep['reported_name']); ?>

                            <?php if (!$rep['reported_is_active']): ?>
                                <span style="color: var(--danger); font-weight: 700;">(User Suspended)</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($rep['status'] === 'pending'): ?>
                            <form method="POST" style="display: flex; gap: 8px;">
                                <?php echo csrfInput(); ?>
                                <input type="hidden" name="report_id" value="<?php echo $rep['id']; ?>">
                                
                                <button type="submit" name="action" value="dismiss" class="btn-secondary-custom" style="padding: 6px 14px; font-size: 0.8rem;">
                                    Dismiss Report
                                </button>

                                <button type="submit" name="action" value="ban_user" class="btn-primary-custom" style="padding: 6px 14px; font-size: 0.8rem; background: var(--danger);" onclick="return confirm('Ban and deactivate reported user account?');">
                                    <i class="fa-solid fa-ban"></i> Ban Reported User
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 40px 20px;">
            <p style="color: var(--text-secondary);">No user reports submitted yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

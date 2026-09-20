<?php
/**
 * ConnectMe - Admin Payments & Transaction Log
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDB();
$statusFilter = $_GET['status'] ?? 'all';

$query = "
    SELECT pay.*, u.email, p.name as user_name, plan.name as plan_name
    FROM payments pay
    JOIN users u ON pay.user_id = u.id
    LEFT JOIN profiles p ON p.user_id = u.id
    JOIN plans plan ON pay.plan_id = plan.id
";

if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'paid', 'failed'])) {
    $query .= " WHERE pay.status = " . $db->quote($statusFilter);
}

$query .= " ORDER BY pay.created_at DESC LIMIT 100";

$stmt = $db->query($query);
$payments = $stmt->fetchAll();
?>

<div style="max-width: 1100px; margin: 30px auto; padding: 0 15px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <h2><i class="fa-solid fa-receipt" style="color: var(--success);"></i> Payment Transactions Log</h2>

        <div style="display: flex; gap: 8px;">
            <a href="<?php echo APP_URL; ?>/admin/payments.php?status=all" class="<?php echo $statusFilter === 'all' ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>" style="padding: 6px 14px; font-size: 0.85rem;">All</a>
            <a href="<?php echo APP_URL; ?>/admin/payments.php?status=paid" class="<?php echo $statusFilter === 'paid' ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>" style="padding: 6px 14px; font-size: 0.85rem;">Paid</a>
            <a href="<?php echo APP_URL; ?>/admin/payments.php?status=pending" class="<?php echo $statusFilter === 'pending' ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>" style="padding: 6px 14px; font-size: 0.85rem;">Pending</a>
            <a href="<?php echo APP_URL; ?>/admin/payments.php?status=failed" class="<?php echo $statusFilter === 'failed' ? 'btn-primary-custom' : 'btn-secondary-custom'; ?>" style="padding: 6px 14px; font-size: 0.85rem;">Failed</a>
        </div>
    </div>

    <div class="glass-card" style="overflow-x: auto; padding: 0;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color); background: rgba(15, 23, 42, 0.6); color: var(--text-secondary);">
                    <th style="padding: 14px;">Order ID / Txn</th>
                    <th style="padding: 14px;">User</th>
                    <th style="padding: 14px;">Plan</th>
                    <th style="padding: 14px;">Amount</th>
                    <th style="padding: 14px;">Provider</th>
                    <th style="padding: 14px;">Status</th>
                    <th style="padding: 14px;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $pay): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 14px; font-family: monospace; font-size: 0.85rem;">
                                <strong><?php echo e($pay['order_id']); ?></strong>
                                <?php if ($pay['payment_id']): ?>
                                    <div style="color: var(--text-muted);"><?php echo e($pay['payment_id']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px;">
                                <strong><?php echo e($pay['user_name'] ?? 'User'); ?></strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo e($pay['email']); ?></div>
                            </td>
                            <td style="padding: 14px;"><?php echo e($pay['plan_name']); ?></td>
                            <td style="padding: 14px; font-weight: 700;">₹<?php echo number_format($pay['amount'], 2); ?></td>
                            <td style="padding: 14px; font-size: 0.85rem; text-transform: capitalize;"><?php echo e($pay['provider']); ?></td>
                            <td style="padding: 14px;">
                                <?php if ($pay['status'] === 'paid'): ?>
                                    <span style="background: rgba(16, 185, 129, 0.2); color: #a7f3d0; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem;">PAID</span>
                                <?php elseif ($pay['status'] === 'pending'): ?>
                                    <span style="background: rgba(245, 158, 11, 0.2); color: #fde68a; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem;">PENDING</span>
                                <?php else: ?>
                                    <span style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem;">FAILED</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px; font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M j, Y, H:i', strtotime($pay['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">No payment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

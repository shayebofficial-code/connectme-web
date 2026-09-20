<?php
/**
 * ConnectMe - Admin Control Center Dashboard
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDB();

// Statistics aggregation queries
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$premiumUsers = (int)$db->query("SELECT COUNT(*) FROM profiles WHERE is_premium = 1")->fetchColumn();

$newRegs = (int)$db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$totalMatches = (int)$db->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$pendingReports = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

$paidRevenue = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid'")->fetchColumn();
$pendingPayments = (int)$db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
?>

<div style="max-width: 1100px; margin: 30px auto; padding: 0 15px;">
    
    <!-- Admin Header -->
    <div class="glass-card" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.8rem; color: var(--warning); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-user-shield"></i> ConnectMe Admin Portal
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Platform management, user safety moderation, and revenue tracking.
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="<?php echo APP_URL; ?>/admin/users.php" class="btn-primary-custom" style="padding: 8px 16px; font-size: 0.85rem;">Manage Users</a>
            <a href="<?php echo APP_URL; ?>/admin/reports.php" class="btn-secondary-custom" style="padding: 8px 16px; font-size: 0.85rem; color: var(--warning);">
                Reports (<?php echo $pendingReports; ?>)
            </a>
        </div>
    </div>

    <!-- Admin Nav Tabs -->
    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 24px;">
        <a href="<?php echo APP_URL; ?>/admin/index.php" class="btn-primary-custom" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="<?php echo APP_URL; ?>/admin/users.php" class="btn-secondary-custom" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-users"></i> Users</a>
        <a href="<?php echo APP_URL; ?>/admin/reports.php" class="btn-secondary-custom" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation"></i> Reports</a>
        <a href="<?php echo APP_URL; ?>/admin/profiles.php" class="btn-secondary-custom" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-image"></i> Profiles & Photos</a>
        <a href="<?php echo APP_URL; ?>/admin/plans.php" class="btn-secondary-custom" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-tags"></i> Subscription Plans</a>
        <a href="<?php echo APP_URL; ?>/admin/payments.php" class="btn-secondary-custom" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-receipt"></i> Payments</a>
        <a href="<?php echo APP_URL; ?>/admin/settings.php" class="btn-secondary-custom" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-sliders"></i> System Config</a>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 30px;">
        <div class="glass-card" style="text-align: center;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary);"><?php echo number_format($totalUsers); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fa-solid fa-users"></i> Total Registered</div>
        </div>

        <div class="glass-card" style="text-align: center;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--success);"><?php echo number_format($activeUsers); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fa-solid fa-user-check"></i> Active Accounts</div>
        </div>

        <div class="glass-card" style="text-align: center;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--gold);"><?php echo number_format($premiumUsers); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fa-solid fa-crown"></i> VIP Premium Users</div>
        </div>

        <div class="glass-card" style="text-align: center;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--secondary);"><?php echo number_format($totalMatches); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fa-solid fa-heart"></i> Total Mutual Matches</div>
        </div>

        <div class="glass-card" style="text-align: center;">
            <div style="font-size: 2.2rem; font-weight: 800; color: #38bdf8;">₹<?php echo number_format($paidRevenue, 2); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fa-solid fa-indian-rupee-sign"></i> Total Paid Revenue</div>
        </div>

        <div class="glass-card" style="text-align: center;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--danger);"><?php echo number_format($pendingReports); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem;"><i class="fa-solid fa-flag"></i> Pending Reports</div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

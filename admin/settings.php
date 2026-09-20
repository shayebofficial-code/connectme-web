<?php
/**
 * ConnectMe - Admin Global System Configuration
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

?>

<div style="max-width: 700px; margin: 30px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="margin-bottom: 8px;"><i class="fa-solid fa-sliders" style="color: var(--secondary);"></i> System Settings & Status</h2>
        <p style="color: var(--text-secondary); margin-bottom: 24px;">
            Overview of application environment and hosting status.
        </p>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--surface-light); border-radius: 8px;">
                <strong>Application Name:</strong>
                <span><?php echo APP_NAME; ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--surface-light); border-radius: 8px;">
                <strong>Environment:</strong>
                <span style="text-transform: capitalize; color: var(--warning);"><?php echo APP_ENV; ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--surface-light); border-radius: 8px;">
                <strong>PHP Version:</strong>
                <span><?php echo PHP_VERSION; ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--surface-light); border-radius: 8px;">
                <strong>Mock Payment Mode:</strong>
                <span><?php echo ENABLE_MOCK_PAYMENT ? 'ENABLED (Test Mode)' : 'DISABLED (Live Razorpay)'; ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--surface-light); border-radius: 8px;">
                <strong>Google Client ID Configured:</strong>
                <span><?php echo strpos(GOOGLE_CLIENT_ID, 'YOUR_GOOGLE') === false ? 'YES' : 'DEFAULT PLACEHOLDER'; ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--surface-light); border-radius: 8px;">
                <strong>Upload Directory Writable:</strong>
                <span><?php echo is_writable(UPLOAD_DIR) ? 'YES (Writable)' : 'NO (Check Permissions)'; ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

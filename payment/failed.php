<?php
/**
 * ConnectMe - Payment Failed Screen
 */

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 480px; margin: 50px auto; text-align: center; padding: 0 15px;">
    <div class="glass-card" style="border-color: var(--danger);">
        <i class="fa-solid fa-circle-xmark" style="font-size: 3.5rem; color: var(--danger); margin-bottom: 16px;"></i>
        <h2 style="margin-bottom: 10px;">Payment Failed or Cancelled</h2>
        <p style="color: var(--text-secondary); margin-bottom: 24px;">
            Your transaction could not be completed. No funds were charged to your account.
        </p>

        <div style="display: flex; gap: 12px; justify-content: center;">
            <a href="<?php echo APP_URL; ?>/user/subscribe.php" class="btn-primary-custom">
                <i class="fa-solid fa-rotate-right"></i> Try Again
            </a>
            <a href="<?php echo APP_URL; ?>/user/dashboard.php" class="btn-secondary-custom">
                Return to Dashboard
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

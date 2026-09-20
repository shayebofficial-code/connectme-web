<?php
/**
 * ConnectMe - Forgot Password Request Page
 */

require_once __DIR__ . '/../includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always show friendly message to prevent email enumeration
        $message = 'If an account exists with this email address, password reset instructions have been sent.';
    }
}
?>

<div style="max-width: 440px; margin: 40px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="text-align: center; margin-bottom: 8px;">Reset Password</h2>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px; font-size: 0.95rem;">
            Enter your email to receive password recovery instructions
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo e($message); ?></div>
        <?php else: ?>
            <form action="<?php echo APP_URL; ?>/auth/forgot-password.php" method="POST">
                <?php echo csrfInput(); ?>

                <div style="margin-bottom: 20px;">
                    <label class="form-label-custom">Email Address</label>
                    <input type="email" name="email" class="form-control-custom" required placeholder="you@example.com">
                </div>

                <button type="submit" class="btn-primary-custom" style="width: 100%;">
                    Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-secondary);">
            Remember your password? <a href="<?php echo APP_URL; ?>/auth/login.php">Log In</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * ConnectMe - Contact Support Page
 */

require_once __DIR__ . '/includes/header.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $name    = trim($_POST['name'] ?? '');
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || !$email || empty($message)) {
        $error = 'Please complete all required fields with a valid email address.';
    } else {
        $success = true;
    }
}
?>

<div style="max-width: 540px; margin: 40px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="text-align: center; margin-bottom: 8px;">Contact Support</h2>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px;">
            Have a question or need assistance? Send our team a message.
        </p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Thank you! Your message has been received. Our support team will get back to you shortly.
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form action="<?php echo APP_URL; ?>/contact.php" method="POST">
                <?php echo csrfInput(); ?>

                <div style="margin-bottom: 16px;">
                    <label class="form-label-custom">Your Name</label>
                    <input type="text" name="name" class="form-control-custom" required placeholder="John Doe">
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="form-label-custom">Email Address</label>
                    <input type="email" name="email" class="form-control-custom" required placeholder="you@example.com">
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="form-label-custom">Subject</label>
                    <input type="text" name="subject" class="form-control-custom" placeholder="Subscription or account inquiry">
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="form-label-custom">Message</label>
                    <textarea name="message" class="form-control-custom" rows="4" required placeholder="Describe your question or issue..."></textarea>
                </div>

                <button type="submit" class="btn-primary-custom" style="width: 100%;">
                    Submit Inquiry
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

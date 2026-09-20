<?php
/**
 * ConnectMe - User Login Page
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/google.php';

// If already logged in, redirect to discover page
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/user/discover.php');
    exit;
}

$errors = [];
$email = '';

// Basic Brute-Force Rate Limiting Check
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
if ($_SESSION['login_attempts'] > 10 && (time() - ($_SESSION['last_attempt_time'] ?? 0)) < 300) {
    $errors[] = 'Too many failed login attempts. Please wait 5 minutes before trying again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    verifyCSRF();

    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || empty($password)) {
        $errors[] = 'Please enter both email and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, password_hash, is_admin, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['password_hash'] && password_verify($password, $user['password_hash'])) {
            if (!$user['is_active']) {
                $errors[] = 'Your account has been deactivated or suspended.';
            } else {
                // Success: Reset attempt counters and log in
                $_SESSION['login_attempts'] = 0;
                loginUser((int)$user['id'], (bool)$user['is_admin']);
                setFlashMessage('success', 'Logged in successfully.');
                
                // Redirect admin to admin dashboard or regular user to discover
                if ($user['is_admin']) {
                    header('Location: ' . APP_URL . '/admin/index.php');
                } else {
                    header('Location: ' . APP_URL . '/user/discover.php');
                }
                exit;
            }
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $errors[] = 'Invalid email address or password.';
        }
    }
}

// Generate Google Auth URL
$oauthState = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $oauthState;
$googleAuthUrl = getGoogleAuthUrl($oauthState);
?>

<div style="max-width: 440px; margin: 40px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="text-align: center; margin-bottom: 8px;">Welcome Back</h2>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px; font-size: 0.95rem;">
            Log in to your ConnectMe account
        </p>

        <!-- Google OAuth Button -->
        <a href="<?php echo e($googleAuthUrl); ?>" class="btn-google" style="margin-bottom: 20px;">
            <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
            Continue with Google
        </a>

        <div style="display: flex; align-items: center; margin: 20px 0; color: var(--text-muted);">
            <hr style="flex: 1; border-color: var(--border-color);">
            <span style="padding: 0 10px; font-size: 0.85rem;">OR LOG IN WITH EMAIL</span>
            <hr style="flex: 1; border-color: var(--border-color);">
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo APP_URL; ?>/auth/login.php" method="POST">
            <?php echo csrfInput(); ?>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">Email Address</label>
                <input type="email" name="email" class="form-control-custom" value="<?php echo e($email); ?>" required placeholder="you@example.com">
            </div>

            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label-custom" style="margin-bottom: 0;">Password</label>
                    <a href="<?php echo APP_URL; ?>/auth/forgot-password.php" style="font-size: 0.85rem;">Forgot?</a>
                </div>
                <input type="password" name="password" class="form-control-custom" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary-custom" style="width: 100%;">
                Log In
            </button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-secondary);">
            Don't have an account? <a href="<?php echo APP_URL; ?>/auth/register.php">Create Free Profile</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

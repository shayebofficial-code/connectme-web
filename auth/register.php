<?php
/**
 * ConnectMe - User Registration Page (Strictly 18+)
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/google.php';

// If already logged in, redirect to discover
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/user/discover.php');
    exit;
}

$errors = [];
$name = '';
$email = '';
$dob = '';
$gender = 'female';
$city = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $name     = trim($_POST['name'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $dob      = trim($_POST['dob'] ?? '');
    $gender   = trim($_POST['gender'] ?? '');
    $city     = trim($_POST['city'] ?? '');

    // Validations
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }

    if (!$email) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if (empty($dob)) {
        $errors[] = 'Date of birth is required.';
    } else {
        $age = calculateAge($dob);
        if ($age < 18) {
            $errors[] = 'You must be at least 18 years old to register on ConnectMe.';
        }
    }

    if (!in_array($gender, ['male', 'female', 'non-binary', 'other'])) {
        $errors[] = 'Please select a valid gender option.';
    }

    if (empty($city)) {
        $errors[] = 'City is required.';
    }

    // Check duplicate email
    if (empty($errors)) {
        $db = getDB();
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $errors[] = 'This email address is already registered. Please log in.';
        }
    }

    // Process Registration
    if (empty($errors)) {
        $db = getDB();
        $db->beginTransaction();

        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $userStmt = $db->prepare("INSERT INTO users (email, password_hash, email_verified) VALUES (?, ?, 1)");
            $userStmt->execute([$email, $passwordHash]);
            $userId = (int)$db->lastInsertId();

            $age = calculateAge($dob);
            $profileStmt = $db->prepare("
                INSERT INTO profiles (user_id, name, dob, age, gender, city, photo)
                VALUES (?, ?, ?, ?, ?, ?, 'default.jpg')
            ");
            $profileStmt->execute([$userId, $name, $dob, $age, $gender, $city]);

            $db->commit();

            // Log in user automatically
            loginUser($userId);
            setFlashMessage('success', 'Welcome to ConnectMe! Your account has been created successfully.');
            header('Location: ' . APP_URL . '/user/profile.php');
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Registration Error: " . $e->getMessage());
            $errors[] = 'An error occurred during registration. Please try again.';
        }
    }
}

// Generate Google Auth URL & State Token
$oauthState = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $oauthState;
$googleAuthUrl = getGoogleAuthUrl($oauthState);
?>

<div style="max-width: 460px; margin: 40px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="text-align: center; margin-bottom: 8px;">Create Account</h2>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px; font-size: 0.95rem;">
            Join ConnectMe to discover genuine connections (18+ only)
        </p>

        <!-- Google OAuth Button -->
        <a href="<?php echo e($googleAuthUrl); ?>" class="btn-google" style="margin-bottom: 20px;">
            <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
            Continue with Google
        </a>

        <div style="display: flex; align-items: center; margin: 20px 0; color: var(--text-muted);">
            <hr style="flex: 1; border-color: var(--border-color);">
            <span style="padding: 0 10px; font-size: 0.85rem;">OR REGISTER WITH EMAIL</span>
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

        <form action="<?php echo APP_URL; ?>/auth/register.php" method="POST">
            <?php echo csrfInput(); ?>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">Full Name</label>
                <input type="text" name="name" class="form-control-custom" value="<?php echo e($name); ?>" required placeholder="John Doe">
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">Email Address</label>
                <input type="email" name="email" class="form-control-custom" value="<?php echo e($email); ?>" required placeholder="you@example.com">
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">Password (Min. 8 characters)</label>
                <input type="password" name="password" class="form-control-custom" required placeholder="••••••••">
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">Date of Birth (Must be 18+)</label>
                <input type="date" name="dob" class="form-control-custom" value="<?php echo e($dob); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div>
                    <label class="form-label-custom">Gender</label>
                    <select name="gender" class="form-control-custom" required>
                        <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="non-binary" <?php echo $gender === 'non-binary' ? 'selected' : ''; ?>>Non-Binary</option>
                        <option value="other" <?php echo $gender === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label-custom">City</label>
                    <input type="text" name="city" class="form-control-custom" value="<?php echo e($city); ?>" required placeholder="Mumbai">
                </div>
            </div>

            <button type="submit" class="btn-primary-custom" style="width: 100%;">
                Create Free Account
            </button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-secondary);">
            Already have an account? <a href="<?php echo APP_URL; ?>/auth/login.php">Log In</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

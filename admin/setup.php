<?php
/**
 * ConnectMe - Initial One-Time Admin Setup Wizard
 *
 * IMPORTANT: Delete or disable this file after initial admin setup!
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $name     = trim($_POST['name'] ?? 'Super Admin');

    if (!$email || strlen($password) < 8) {
        $error = 'Please provide a valid email and a password of at least 8 characters.';
    } else {
        $db = getDB();

        // Check if admin already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Upgrade existing user to admin
            $update = $db->prepare("UPDATE users SET is_admin = 1, is_active = 1 WHERE id = ?");
            $update->execute([$existing['id']]);
            $message = "Existing user [{$email}] upgraded to Admin successfully!";
        } else {
            // Create new admin account
            $db->beginTransaction();
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insUser = $db->prepare("INSERT INTO users (email, password_hash, email_verified, is_admin) VALUES (?, ?, 1, 1)");
                $insUser->execute([$email, $hash]);
                $adminId = $db->lastInsertId();

                $insProfile = $db->prepare("INSERT INTO profiles (user_id, name, dob, age, gender, city, photo, is_verified, is_premium) VALUES (?, ?, '1990-01-01', 34, 'other', 'Admin HQ', 'default.jpg', 1, 1)");
                $insProfile->execute([$adminId, $name]);

                $db->commit();
                $message = "New Admin account [{$email}] created successfully!";
            } catch (Exception $e) {
                $db->rollBack();
                $error = "Setup failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ConnectMe - One-Time Admin Setup</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px;">
    <div class="glass-card" style="max-width:440px; width:100%;">
        <h2 style="text-align:center; margin-bottom:10px; color:var(--warning);">
            ⚙️ Initial Admin Setup
        </h2>
        <p style="text-align:center; color:var(--text-secondary); font-size:0.9rem; margin-bottom:20px;">
            Create or upgrade your initial administrator account. Delete this file after setup.
        </p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo e($message); ?></div>
            <p style="text-align:center;"><a href="<?php echo APP_URL; ?>/auth/login.php" class="btn-primary-custom">Go to Login Page</a></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="margin-bottom:16px;">
                    <label class="form-label-custom">Admin Name</label>
                    <input type="text" name="name" class="form-control-custom" value="Super Admin" required>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-custom">Admin Email</label>
                    <input type="email" name="email" class="form-control-custom" placeholder="admin@connectme.com" required>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="form-label-custom">Admin Password (Min. 8 chars)</label>
                    <input type="password" name="password" class="form-control-custom" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary-custom" style="width:100%;">
                    Create / Upgrade Admin Account
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

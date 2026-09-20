<?php
/**
 * ConnectMe - View & Edit Profile Page
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();
$profile = getUserProfileData($userId);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $name       = trim($_POST['name'] ?? '');
    $dob        = trim($_POST['dob'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $lookingFor = trim($_POST['looking_for'] ?? 'everyone');
    $city       = trim($_POST['city'] ?? '');
    $bio        = trim($_POST['bio'] ?? '');
    $interests  = trim($_POST['interests'] ?? '');

    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Full name cannot be empty.';
    }

    if (empty($dob)) {
        $errors[] = 'Date of birth is required.';
    } else {
        $age = calculateAge($dob);
        if ($age < 18) {
            $errors[] = 'You must be at least 18 years old to use ConnectMe.';
        }
    }

    if (empty($city)) {
        $errors[] = 'City is required.';
    }

    $photoFilename = $profile['photo'] ?? 'default.jpg';

    // Handle Photo Upload if selected
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = uploadProfileImage($_FILES['photo']);
        if ($uploadResult['success']) {
            $photoFilename = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }

    if (empty($errors)) {
        $age = calculateAge($dob);
        $updateStmt = $db->prepare("
            UPDATE profiles
            SET name = ?, dob = ?, age = ?, gender = ?, looking_for = ?, city = ?, bio = ?, interests = ?, photo = ?
            WHERE user_id = ?
        ");
        $updateStmt->execute([$name, $dob, $age, $gender, $lookingFor, $city, $bio, $interests, $photoFilename, $userId]);

        setFlashMessage('success', 'Your profile has been updated successfully!');
        header('Location: ' . APP_URL . '/user/profile.php');
        exit;
    }
}
?>

<div style="max-width: 680px; margin: 30px auto; padding: 0 15px;">
    <div class="glass-card">
        <h2 style="margin-bottom: 6px;">Edit Dating Profile</h2>
        <p style="color: var(--text-secondary); margin-bottom: 24px; font-size: 0.95rem;">
            Keep your profile fresh and attractive to get better matches!
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo APP_URL; ?>/user/profile.php" method="POST" enctype="multipart/form-data">
            <?php echo csrfInput(); ?>

            <!-- Photo Upload Box -->
            <div style="text-align: center; margin-bottom: 24px;">
                <div style="position: relative; display: inline-block;">
                    <img id="photoPreview" 
                         src="<?php echo APP_URL . '/uploads/profiles/' . e($profile['photo'] ?? 'default.jpg'); ?>" 
                         alt="Profile Photo"
                         style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); box-shadow: var(--shadow-md);"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($profile['name'] ?? 'User'); ?>&background=e11d48&color=fff';">
                    
                    <label for="photoInput" style="position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid var(--surface);">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                </div>
                <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/webp" style="display: none;">
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;">
                    Max 5 MB (JPG, PNG, WebP). Random file names generated automatically for security.
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label-custom">Full Name</label>
                    <input type="text" name="name" class="form-control-custom" value="<?php echo e($profile['name'] ?? ''); ?>" required>
                </div>
                <div>
                    <label class="form-label-custom">Date of Birth (18+)</label>
                    <input type="date" name="dob" class="form-control-custom" value="<?php echo e($profile['dob'] ?? ''); ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label-custom">Gender</label>
                    <select name="gender" class="form-control-custom" required>
                        <option value="female" <?php echo ($profile['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="male" <?php echo ($profile['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="non-binary" <?php echo ($profile['gender'] ?? '') === 'non-binary' ? 'selected' : ''; ?>>Non-Binary</option>
                        <option value="other" <?php echo ($profile['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label-custom">Interested In</label>
                    <select name="looking_for" class="form-control-custom">
                        <option value="everyone" <?php echo ($profile['looking_for'] ?? '') === 'everyone' ? 'selected' : ''; ?>>Everyone</option>
                        <option value="male" <?php echo ($profile['looking_for'] ?? '') === 'male' ? 'selected' : ''; ?>>Men</option>
                        <option value="female" <?php echo ($profile['looking_for'] ?? '') === 'female' ? 'selected' : ''; ?>>Women</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">City / Location</label>
                <input type="text" name="city" class="form-control-custom" value="<?php echo e($profile['city'] ?? ''); ?>" required placeholder="Mumbai, Delhi, Bangalore...">
            </div>

            <div style="margin-bottom: 16px;">
                <label class="form-label-custom">About Me (Bio)</label>
                <textarea name="bio" class="form-control-custom" rows="3" placeholder="Tell potential matches about your personality, hobbies, or what you're looking for..."><?php echo e($profile['bio'] ?? ''); ?></textarea>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label-custom">Interests (Comma-separated tags)</label>
                <input type="text" name="interests" class="form-control-custom" value="<?php echo e($profile['interests'] ?? ''); ?>" placeholder="Travel, Music, Fitness, Movies, Coffee">
            </div>

            <button type="submit" class="btn-primary-custom" style="width: 100%;">
                <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

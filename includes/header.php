<?php
/**
 * ConnectMe - Global Header Template
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

$currentUserId = currentUserId();
$userProfile = $currentUserId ? getUserProfileData($currentUserId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' . APP_NAME : APP_NAME . ' - ' . APP_TAGLINE; ?></title>
    <meta name="description" content="ConnectMe is a modern, secure online dating platform to meet genuine adults, discover matches, and chat safely.">
    <meta name="csrf-token" content="<?php echo e(getCSRFToken()); ?>">
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Style System -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar-custom">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <a href="<?php echo APP_URL; ?>/index.php" class="navbar-brand">
                <i class="fa-solid fa-heart-pulse"></i> ConnectMe
            </a>

            <div style="display: flex; align-items: center; gap: 20px;">
                <?php if (isLoggedIn()): ?>
                    <div style="display: none; @media (min-width: 992px) { display: flex; } align-items: center; gap: 16px;">
                        <a href="<?php echo APP_URL; ?>/user/discover.php" style="color: var(--text-primary); font-weight: 500;"><i class="fa-solid fa-fire"></i> Discover</a>
                        <a href="<?php echo APP_URL; ?>/user/matches.php" style="color: var(--text-primary); font-weight: 500;"><i class="fa-solid fa-comments"></i> Matches</a>
                        <a href="<?php echo APP_URL; ?>/user/likes.php" style="color: var(--text-primary); font-weight: 500;"><i class="fa-solid fa-heart"></i> Likes</a>
                        <a href="<?php echo APP_URL; ?>/user/subscribe.php" style="color: var(--gold); font-weight: 600;"><i class="fa-solid fa-crown"></i> Premium</a>
                        
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo APP_URL; ?>/admin/index.php" style="color: var(--warning); font-weight: 600;"><i class="fa-solid fa-user-shield"></i> Admin</a>
                        <?php endif; ?>
                    </div>

                    <!-- User Profile Dropdown / Avatar -->
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <a href="<?php echo APP_URL; ?>/user/profile.php" style="display: flex; align-items: center; gap: 8px; color: white;">
                            <img src="<?php echo APP_URL . '/uploads/profiles/' . e($userProfile['photo'] ?? 'default.jpg'); ?>" 
                                 alt="Profile" 
                                 style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($userProfile['name'] ?? 'User'); ?>&background=e11d48&color=fff';">
                            <span style="font-weight: 600; display: none; @media(min-width: 768px){ display: inline; }"><?php echo e($userProfile['name'] ?? 'Account'); ?></span>
                        </a>
                        <a href="<?php echo APP_URL; ?>/auth/logout.php" class="btn-secondary-custom" style="padding: 6px 14px; font-size: 0.85rem;" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; gap: 12px;">
                        <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn-secondary-custom" style="padding: 8px 18px; font-size: 0.9rem;">Log In</a>
                        <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn-primary-custom" style="padding: 8px 18px; font-size: 0.9rem;">Join Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Global Container for Flash Messages -->
    <div style="max-width: 1200px; margin: 15px auto 0 auto; padding: 0 15px;">
        <?php displayFlashMessages(); ?>
    </div>

<?php
/**
 * ConnectMe - Modern Homepage & Landing Page
 */

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/google.php';

$oauthState = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $oauthState;
$googleAuthUrl = getGoogleAuthUrl($oauthState);
?>

<!-- Hero Banner Section -->
<section style="padding: 60px 20px; text-align: center; background: radial-gradient(circle at top, rgba(225, 29, 72, 0.15) 0%, transparent 70%);">
    <div style="max-width: 800px; margin: 0 auto;">
        <span style="background: rgba(225, 29, 72, 0.15); border: 1px solid var(--primary); color: #f43f5e; font-size: 0.85rem; font-weight: 700; padding: 6px 16px; border-radius: 999px; text-transform: uppercase; letter-spacing: 1px; display: inline-block; margin-bottom: 20px;">
            ❤️ Real Connections • Adults 18+ Only
        </span>

        <h1 style="font-size: 3.2rem; font-weight: 800; margin-bottom: 16px; line-height: 1.15; background: linear-gradient(135deg, #ffffff 30%, #cbd5e1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Meet someone genuine.
        </h1>

        <p style="font-size: 1.2rem; color: var(--text-secondary); max-width: 620px; margin: 0 auto 30px auto;">
            Discover people near you, make meaningful connections, and chat effortlessly with your mutual matches.
        </p>

        <div style="display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; margin-bottom: 40px;">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo APP_URL; ?>/user/discover.php" class="btn-primary-custom" style="padding: 14px 32px; font-size: 1.1rem;">
                    <i class="fa-solid fa-fire"></i> Discover People Now
                </a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn-primary-custom" style="padding: 14px 30px; font-size: 1.05rem;">
                    <i class="fa-solid fa-user-plus"></i> Create Free Profile
                </a>
                <a href="<?php echo e($googleAuthUrl); ?>" class="btn-secondary-custom" style="padding: 14px 24px; font-size: 1rem; background: white; color: #1f2937;">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                    Continue with Google
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section style="padding: 50px 20px; max-width: 1100px; margin: 0 auto;">
    <h2 style="text-align: center; font-size: 2.2rem; margin-bottom: 40px;">How ConnectMe Works</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
        <div class="glass-card" style="text-align: center;">
            <div style="width: 60px; height: 60px; background: rgba(225, 29, 72, 0.2); color: var(--primary); font-size: 1.8rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <h3 style="font-size: 1.3rem; margin-bottom: 10px;">1. Create Profile</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">
                Set up your 18+ profile, upload photos, and share your city, bio and personal interests.
            </p>
        </div>

        <div class="glass-card" style="text-align: center;">
            <div style="width: 60px; height: 60px; background: rgba(139, 92, 246, 0.2); color: var(--secondary); font-size: 1.8rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <i class="fa-solid fa-heart"></i>
            </div>
            <h3 style="font-size: 1.3rem; margin-bottom: 10px;">2. Discover & Like</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">
                Browse curated profile cards in your area. Like profiles that catch your interest!
            </p>
        </div>

        <div class="glass-card" style="text-align: center;">
            <div style="width: 60px; height: 60px; background: rgba(16, 185, 129, 0.2); color: var(--success); font-size: 1.8rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <i class="fa-solid fa-comments"></i>
            </div>
            <h3 style="font-size: 1.3rem; margin-bottom: 10px;">3. Match & Chat</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">
                When interest is mutual, a match is created! Start chatting instantly in a secure space.
            </p>
        </div>
    </div>
</section>

<!-- Safety & Trust Guarantee Section -->
<section style="padding: 50px 20px; background: var(--surface); border-y: 1px solid var(--border-color); margin: 40px 0;">
    <div style="max-width: 900px; margin: 0 auto; text-align: center;">
        <i class="fa-solid fa-shield-halved" style="font-size: 3rem; color: var(--primary); margin-bottom: 16px;"></i>
        <h2 style="font-size: 2rem; margin-bottom: 16px;">Your Safety is Our Top Priority</h2>
        <p style="color: var(--text-secondary); font-size: 1.05rem; line-height: 1.7; margin-bottom: 30px;">
            We enforce strict age verification (18+ only), server-side photo validation, blocking tools, and user reporting mechanisms to keep your dating experience authentic and secure.
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
            <a href="<?php echo APP_URL; ?>/safety.php" class="btn-secondary-custom"><i class="fa-solid fa-book-open"></i> Read Safety Rules</a>
            <a href="<?php echo APP_URL; ?>/user/subscribe.php" class="btn-primary-custom"><i class="fa-solid fa-crown"></i> View Premium Features</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

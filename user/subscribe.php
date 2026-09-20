<?php
/**
 * ConnectMe - Premium Subscription Plans Page
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

// Fetch plans stored in database
$stmt = $db->query("SELECT * FROM plans WHERE is_active = 1 ORDER BY price ASC");
$plans = $stmt->fetchAll();

$isPremium = checkUserPremiumStatus($userId);
$userProfile = getUserProfileData($userId);
?>

<div style="max-width: 960px; margin: 30px auto; padding: 0 15px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <span class="badge-premium" style="font-size: 0.9rem; padding: 6px 16px; margin-bottom: 12px; display: inline-block;">
            <i class="fa-solid fa-crown"></i> ConnectMe Premium
        </span>
        <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Upgrade Your Dating Life</h1>
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto; font-size: 1.05rem;">
            Get instant access to top profile visibility, unlimited likes, direct messaging, and see everyone who likes you!
        </p>
    </div>

    <?php if ($isPremium): ?>
        <div class="glass-card" style="background: rgba(16, 185, 129, 0.15); border-color: var(--success); text-align: center; padding: 24px; margin-bottom: 30px;">
            <h3 style="color: var(--success); margin-bottom: 6px;"><i class="fa-solid fa-circle-check"></i> You Are Premium Active!</h3>
            <p style="color: var(--text-primary); font-size: 0.95rem;">
                Your subscription is active until <strong><?php echo date('F j, Y, g:i a', strtotime($userProfile['premium_until'])); ?></strong>.
            </p>
        </div>
    <?php endif; ?>

    <!-- Pricing Grid -->
    <div class="pricing-grid">
        <?php foreach ($plans as $plan): ?>
            <?php 
            $features = json_decode($plan['features'] ?? '[]', true);
            $isPopular = ($plan['duration_months'] == 3); // Highlight quarterly plan
            ?>
            <div class="pricing-card <?php echo $isPopular ? 'popular' : ''; ?>">
                <?php if ($isPopular): ?>
                    <div class="badge-popular">MOST POPULAR</div>
                <?php endif; ?>

                <h3 style="font-size: 1.4rem; margin-top: 10px; color: var(--text-primary);"><?php echo e($plan['name']); ?></h3>
                <div style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo $plan['duration_months']; ?> Month<?php echo $plan['duration_months'] > 1 ? 's' : ''; ?> Access</div>

                <div class="pricing-price">
                    ₹<?php echo number_format($plan['price'], 0); ?>

                </div>

                <ul style="list-style: none; padding: 0; margin: 20px 0; text-align: left; font-size: 0.9rem; color: var(--text-secondary); line-height: 2;">
                    <?php if (!empty($features)): ?>
                        <?php foreach ($features as $feat): ?>
                            <li><i class="fa-solid fa-check" style="color: var(--success); margin-right: 8px;"></i> <?php echo e($feat); ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <form action="<?php echo APP_URL; ?>/payment/create-order.php" method="POST">
                    <?php echo csrfInput(); ?>

                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                    <button type="submit" class="btn-primary-custom" style="width: 100%; <?php echo $isPopular ? 'background: linear-gradient(135deg, var(--gold), #d97706);' : ''; ?>">
                        <i class="fa-solid fa-bolt"></i> Subscribe Now
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 30px;">
        <i class="fa-solid fa-shield-halved"></i> 100% Safe & Secure Encryption. Cancel anytime from your host account.
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

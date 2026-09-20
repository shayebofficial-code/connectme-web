<?php
/**
 * ConnectMe - Admin Subscription Plans Manager
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    $action         = $_POST['action'] ?? '';
    $planId         = (int)($_POST['plan_id'] ?? 0);
    $name           = trim($_POST['name'] ?? '');
    $durationMonths = (int)($_POST['duration_months'] ?? 1);
    $price          = (float)($_POST['price'] ?? 0.0);
    $featuresJson   = trim($_POST['features'] ?? '[]');

    if ($action === 'create' && !empty($name) && $price > 0) {
        $stmt = $db->prepare("INSERT INTO plans (name, duration_months, price, currency, features, is_active) VALUES (?, ?, ?, 'INR', ?, 1)");
        $stmt->execute([$name, $durationMonths, $price, $featuresJson]);
        setFlashMessage('success', 'New plan created.');
    } elseif ($action === 'toggle_active' && $planId) {
        $db->prepare("UPDATE plans SET is_active = NOT is_active WHERE id = ?")->execute([$planId]);
        setFlashMessage('success', 'Plan active status updated.');
    }
    header('Location: ' . APP_URL . '/admin/plans.php');
    exit;
}

$plans = $db->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll();
?>

<div style="max-width: 1000px; margin: 30px auto; padding: 0 15px;">
    <h2><i class="fa-solid fa-tags" style="color: var(--gold);"></i> Manage Subscription Plans</h2>
    <p style="color: var(--text-secondary); margin-bottom: 24px;">
        Control subscription plans, pricing, and active status stored in MySQL.
    </p>

    <!-- Create New Plan Form -->
    <div class="glass-card" style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 14px;">Add New Subscription Plan</h3>
        <form method="POST" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end;">
            <?php echo csrfInput(); ?>
            <input type="hidden" name="action" value="create">

            <div>
                <label class="form-label-custom">Plan Name</label>
                <input type="text" name="name" class="form-control-custom" placeholder="e.g. Special Deal" required>
            </div>

            <div>
                <label class="form-label-custom">Duration (Months)</label>
                <input type="number" name="duration_months" class="form-control-custom" value="1" min="1" max="24" required>
            </div>

            <div>
                <label class="form-label-custom">Price (INR ₹)</label>
                <input type="number" step="0.01" name="price" class="form-control-custom" placeholder="299.00" required>
            </div>

            <div>
                <label class="form-label-custom">Features (JSON Array)</label>
                <input type="text" name="features" class="form-control-custom" value='["Unlimited Likes","Direct Messaging"]'>
            </div>

            <div>
                <button type="submit" class="btn-primary-custom" style="width: 100%;">
                    <i class="fa-solid fa-plus"></i> Create Plan
                </button>
            </div>
        </form>
    </div>

    <!-- Active Plans List -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <?php foreach ($plans as $plan): ?>
            <div class="glass-card" style="position: relative;">
                <h3 style="color: var(--text-primary); font-size: 1.3rem; margin-bottom: 4px;"><?php echo e($plan['name']); ?></h3>
                <div style="color: var(--gold); font-size: 1.8rem; font-weight: 800; margin-bottom: 10px;">
                    ₹<?php echo number_format($plan['price'], 2); ?>

                </div>
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 16px;">
                    Duration: <?php echo $plan['duration_months']; ?> Month(s)
                </div>

                <form method="POST">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                    <button type="submit" name="action" value="toggle_active" class="btn-secondary-custom" style="width: 100%;">
                        <?php echo $plan['is_active'] ? 'Deactivate Plan' : 'Activate Plan'; ?>

                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * ConnectMe - Payment Engine: Order Creation & Checkout Setup
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$planId = (int)($_POST['plan_id'] ?? $_GET['plan_id'] ?? 0);

if (!$planId) {
    setFlashMessage('warning', 'Please select a valid subscription plan.');
    header('Location: ' . APP_URL . '/user/subscribe.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM plans WHERE id = ? AND is_active = 1");
$stmt->execute([$planId]);
$plan = $stmt->fetch();

if (!$plan) {
    setFlashMessage('danger', 'Subscription plan not found or inactive.');
    header('Location: ' . APP_URL . '/user/subscribe.php');
    exit;
}

$amount = (float)$plan['price'];
$currency = $plan['currency'] ?? 'INR';
$orderId = 'ORD_' . time() . '_' . rand(1000, 9999);

// Save initial pending payment record server-side
$paymentStmt = $db->prepare("
    INSERT INTO payments (user_id, plan_id, provider, order_id, amount, currency, status)
    VALUES (?, ?, ?, ?, ?, ?, 'pending')
");
$provider = ENABLE_MOCK_PAYMENT ? 'mock_gateway' : 'razorpay';
$paymentStmt->execute([$userId, $planId, $provider, $orderId, $amount, $currency]);

if (ENABLE_MOCK_PAYMENT): ?>
    <!-- MOCK TEST PAYMENT MODE SCREEN -->
    <div style="max-width: 500px; margin: 40px auto; padding: 0 15px;">
        <div class="glass-card" style="text-align: center; border-color: var(--warning);">
            <div style="background: rgba(245, 158, 11, 0.2); color: var(--warning); padding: 6px 14px; border-radius: 999px; display: inline-block; font-size: 0.85rem; font-weight: 700; margin-bottom: 16px;">
                🧪 MOCK PAYMENT SIMULATOR ACTIVE
            </div>

            <h2 style="margin-bottom: 10px;">Complete Test Subscription</h2>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                You are subscribing to <strong><?php echo e($plan['name']); ?> Plan</strong> (₹<?php echo number_format($amount, 2); ?>).
            </p>

            <div style="background: var(--surface-light); padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px; text-align: left; font-size: 0.9rem; line-height: 1.8;">
                <div><strong>Order ID:</strong> <?php echo e($orderId); ?></div>
                <div><strong>Plan Duration:</strong> <?php echo $plan['duration_months']; ?> Months</div>
                <div><strong>Amount:</strong> ₹<?php echo number_format($amount, 2); ?></div>
                <div><strong>Mode:</strong> Test / Sandbox</div>
            </div>

            <form action="<?php echo APP_URL; ?>/payment/success.php" method="POST">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="order_id" value="<?php echo e($orderId); ?>">
                <input type="hidden" name="payment_id" value="pay_mock_<?php echo bin2hex(random_bytes(8)); ?>">
                <input type="hidden" name="signature" value="mock_signature_valid">

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-primary-custom" style="flex: 1; background: var(--success);">
                        <i class="fa-solid fa-circle-check"></i> Simulate Successful Payment
                    </button>
                    <a href="<?php echo APP_URL; ?>/payment/failed.php?order_id=<?php echo e($orderId); ?>" class="btn-secondary-custom" style="color: var(--danger);">
                        Simulate Failure
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- REAL RAZORPAY PAYMENT GATEWAY CHECKOUT INTEGRATION -->
    <div style="max-width: 500px; margin: 40px auto; text-align: center; padding: 0 15px;">
        <div class="glass-card">
            <h2 style="margin-bottom: 10px;">Redirecting to Payment Gateway...</h2>
            <p style="color: var(--text-secondary); margin-bottom: 24px;">Please complete your transaction securely.</p>
            
            <button id="rzp-button" class="btn-primary-custom" style="width: 100%;">
                Open Razorpay Payment Window
            </button>

            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
            var options = {
                "key": "<?php echo RAZORPAY_KEY_ID; ?>",
                "amount": "<?php echo $amount * 100; ?>", // Amount in paise
                "currency": "INR",
                "name": "ConnectMe Dating",
                "description": "<?php echo e($plan['name']); ?> Plan Subscription",
                "order_id": "<?php echo $orderId; ?>",
                "handler": function (response){
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?php echo APP_URL; ?>/payment/success.php';

                    var fields = {
                        'csrf_token': '<?php echo getCSRFToken(); ?>',
                        'order_id': '<?php echo $orderId; ?>',
                        'razorpay_payment_id': response.razorpay_payment_id,
                        'razorpay_order_id': response.razorpay_order_id,
                        'razorpay_signature': response.razorpay_signature
                    };

                    for (var k in fields) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = k;
                        input.value = fields[k];
                        form.appendChild(input);
                    }
                    document.body.appendChild(form);
                    form.submit();
                },
                "theme": {
                    "color": "#e11d48"
                }
            };
            var rzp = new Razorpay(options);
            document.getElementById('rzp-button').onclick = function(e){
                rzp.open();
                e.preventDefault();
            }
            </script>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

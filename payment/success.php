<?php
/**
 * ConnectMe - Payment Verification & Subscription Activation
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = currentUserId();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/user/subscribe.php');
    exit;
}

verifyCSRF();

$orderId   = trim($_POST['order_id'] ?? $_POST['razorpay_order_id'] ?? '');
$paymentId = trim($_POST['payment_id'] ?? $_POST['razorpay_payment_id'] ?? '');
$signature = trim($_POST['signature'] ?? $_POST['razorpay_signature'] ?? '');

if (empty($orderId)) {
    setFlashMessage('danger', 'Invalid payment confirmation data.');
    header('Location: ' . APP_URL . '/user/subscribe.php');
    exit;
}

// Fetch pending payment order from DB
$stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$payment = $stmt->fetch();

if (!$payment) {
    setFlashMessage('danger', 'Payment record not found.');
    header('Location: ' . APP_URL . '/user/subscribe.php');
    exit;
}

// Signature Verification
$isValidSignature = false;

if (ENABLE_MOCK_PAYMENT) {
    $isValidSignature = true; // In mock test mode, accept simulated signature
} else {
    // Real Razorpay HMAC SHA256 Verification
    $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
    if (hash_equals($generatedSignature, $signature)) {
        $isValidSignature = true;
    }
}

if (!$isValidSignature) {
    $updateFail = $db->prepare("UPDATE payments SET status = 'failed' WHERE id = ?");
    $updateFail->execute([$payment['id']]);

    setFlashMessage('danger', 'Payment verification failed due to invalid signature.');
    header('Location: ' . APP_URL . '/payment/failed.php');
    exit;
}

// Check idempotency: If payment already marked paid, avoid duplicate subscription addition
if ($payment['status'] === 'paid') {
    setFlashMessage('info', 'Subscription is already active for this transaction.');
    header('Location: ' . APP_URL . '/user/dashboard.php');
    exit;
}

// Fetch Plan Duration
$planStmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
$planStmt->execute([$payment['plan_id']]);
$plan = $planStmt->fetch();

$durationMonths = (int)($plan['duration_months'] ?? 1);
$now = new DateTime();

// Calculate subscription dates
$startsAt = $now->format('Y-m-d H:i:s');
$endsAt   = $now->modify("+$durationMonths months")->format('Y-m-d H:i:s');

$db->beginTransaction();
try {
    // 1. Update Payment Record to PAID
    $updatePay = $db->prepare("
        UPDATE payments
        SET payment_id = ?, signature = ?, status = 'paid'
        WHERE id = ?
    ");
    $updatePay->execute([$paymentId, $signature, $payment['id']]);

    // 2. Insert Active Subscription Record
    $insertSub = $db->prepare("
        INSERT INTO subscriptions (user_id, plan_id, provider, provider_payment_id, amount, status, starts_at, ends_at)
        VALUES (?, ?, ?, ?, ?, 'active', ?, ?)
    ");
    $provider = ENABLE_MOCK_PAYMENT ? 'mock_gateway' : 'razorpay';
    $insertSub->execute([$userId, $payment['plan_id'], $provider, $paymentId, $payment['amount'], $startsAt, $endsAt]);

    // 3. Update User Profile Premium Status
    $updateProfile = $db->prepare("
        UPDATE profiles
        SET is_premium = 1, premium_until = ?
        WHERE user_id = ?
    ");
    $updateProfile->execute([$endsAt, $userId]);

    $db->commit();

    setFlashMessage('success', '🎉 Payment successful! Premium features have been unlocked on your account.');
    header('Location: ' . APP_URL . '/user/dashboard.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    error_log("Payment Success Processing Error: " . $e->getMessage());
    setFlashMessage('danger', 'An error occurred while activating your subscription. Please contact support.');
    header('Location: ' . APP_URL . '/user/subscribe.php');
    exit;
}

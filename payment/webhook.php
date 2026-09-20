<?php
/**
 * ConnectMe - Idempotent Payment Webhook Processor
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (empty($payload)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Empty payload']);
    exit;
}

// Verify webhook signature in production mode
if (!ENABLE_MOCK_PAYMENT && !empty(RAZORPAY_KEY_SECRET)) {
    $expectedSig = hash_hmac('sha256', $payload, RAZORPAY_KEY_SECRET);
    if (!hash_equals($expectedSig, $sigHeader)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid webhook signature']);
        exit;
    }
}

$data = json_decode($payload, true);
$event = $data['event'] ?? '';

if ($event === 'payment.captured' || $event === 'order.paid') {
    $entity = $data['payload']['payment']['entity'] ?? $data['payload']['order']['entity'] ?? [];
    $orderId   = $entity['order_id'] ?? '';
    $paymentId = $entity['id'] ?? '';

    if (!empty($orderId)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $payment = $stmt->fetch();

        if ($payment && $payment['status'] !== 'paid') {
            // Fetch Plan Duration
            $planStmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
            $planStmt->execute([$payment['plan_id']]);
            $plan = $planStmt->fetch();
            $durationMonths = (int)($plan['duration_months'] ?? 1);

            $now = new DateTime();
            $startsAt = $now->format('Y-m-d H:i:s');
            $endsAt   = $now->modify("+$durationMonths months")->format('Y-m-d H:i:s');

            $db->beginTransaction();
            try {
                // 1. Mark Payment Paid
                $db->prepare("UPDATE payments SET payment_id = ?, status = 'paid', raw_response = ? WHERE id = ?")
                   ->execute([$paymentId, $payload, $payment['id']]);

                // 2. Create Active Subscription
                $db->prepare("INSERT INTO subscriptions (user_id, plan_id, provider, provider_payment_id, amount, status, starts_at, ends_at) VALUES (?, ?, 'razorpay_webhook', ?, ?, 'active', ?, ?)")
                   ->execute([$payment['user_id'], $payment['plan_id'], $paymentId, $payment['amount'], $startsAt, $endsAt]);

                // 3. Set Profile Premium Until
                $db->prepare("UPDATE profiles SET is_premium = 1, premium_until = ? WHERE user_id = ?")
                   ->execute([$endsAt, $payment['user_id']]);

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Webhook Processing Error: " . $e->getMessage());
            }
        }
    }
}

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Webhook processed']);
exit;

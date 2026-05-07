<?php
require_once __DIR__ . '/lib/env.php';
load_env(__DIR__ . '/.env');

$secret    = getenv('STRIPE_SECRET_KEY');
$sessionId = isset($_GET['session_id']) ? trim($_GET['session_id']) : '';

$session  = null;
$hasError = false;

if ($sessionId && $secret) {
    require_once __DIR__ . '/vendor/autoload.php';
    \Stripe\Stripe::setApiKey($secret);

    try {
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
    } catch (\Exception $e) {
        $hasError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page-wrapper">
    <div class="thank-you-card">
        <?php if ($hasError || !$session): ?>
            <div class="ty-icon ty-icon--error">✕</div>
            <h1>Something went wrong</h1>
            <p>We couldn't find your order details. If you completed a payment, please check your email for a confirmation.</p>
            <a href="index.php" class="ty-btn">Back to checkout</a>

        <?php elseif ($session->payment_status === 'paid'): ?>
            <div class="ty-icon ty-icon--success">✓</div>
            <h1>You're all set!</h1>
            <p>Thanks for your order<?= $session->customer_details->name ? ', ' . htmlspecialchars($session->customer_details->name) : '' ?>. A confirmation has been sent to <strong><?= htmlspecialchars($session->customer_details->email ?? '') ?></strong>.</p>
            <div class="ty-details">
                <div class="ty-row">
                    <span>Order ID</span>
                    <span><?= htmlspecialchars($session->id) ?></span>
                </div>
                <div class="ty-row">
                    <span>Amount paid</span>
                    <span>$<?= number_format($session->amount_total / 100, 2) ?></span>
                </div>
            </div>
            <a href="index.php" class="ty-btn">Back to home</a>

        <?php else: ?>
            <div class="ty-icon ty-icon--pending">⏳</div>
            <h1>Payment pending</h1>
            <p>Your payment is still being processed. We'll send you an email once it's confirmed.</p>
            <a href="index.php" class="ty-btn">Back to home</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../lib/env.php';
load_env(__DIR__ . '/../.env');

$body    = json_decode(file_get_contents('php://input'), true);
$code    = isset($body['code'])    ? trim($body['code'])    : '';
$priceId = isset($body['priceId']) ? trim($body['priceId']) : '';

if ($code === '' || $priceId === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing coupon code or price ID']);
    exit;
}

$secret = getenv('STRIPE_SECRET_KEY');
if (!$secret) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe is not configured']);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

\Stripe\Stripe::setApiKey($secret);

try {
    $coupon = \Stripe\Coupon::retrieve($code);

    if (!$coupon->valid) {
        echo json_encode(['valid' => false, 'message' => 'This coupon has expired or is no longer valid.']);
        exit;
    }

    $discount = 0;
    $label = '';

    if ($coupon->amount_off) {
        $discount = $coupon->amount_off / 100;
        $label = '$' . number_format($discount, 2) . ' off';
    } elseif ($coupon->percent_off) {
        $label = $coupon->percent_off . '% off';
    }

    echo json_encode([
        'valid' => true,
        'couponId' => $coupon->id,
        'amountOff' => $coupon->amount_off ? $coupon->amount_off / 100 : null,
        'percentOff' => $coupon->percent_off,
        'label' => $label,
        'message' => 'Coupon applied: ' . $label
    ]);

} catch (\Stripe\Exception\InvalidRequestException $e) {
    echo json_encode(['valid' => false, 'message' => 'Coupon not found. Please check the code and try again.']);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Something went wrong, please try again.']);
}

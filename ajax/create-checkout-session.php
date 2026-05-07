<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../lib/env.php';
load_env(__DIR__ . '/../.env');

$secret = getenv('STRIPE_SECRET_KEY');
if (!$secret) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe is not configured.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body.']);
    exit;
}

$priceId   = isset($body['priceId'])   ? trim($body['priceId'])   : '';
$couponId  = isset($body['couponId'])  ? trim($body['couponId'])  : '';
$email     = isset($body['email'])     ? trim($body['email'])     : '';
$firstName = isset($body['firstName']) ? trim($body['firstName']) : '';
$lastName  = isset($body['lastName'])  ? trim($body['lastName'])  : '';

$street = isset($body['street']) ? trim($body['street']) : '';
$city   = isset($body['city'])   ? trim($body['city'])   : '';
$state  = isset($body['state'])  ? strtoupper(trim($body['state'])) : '';
$zip    = isset($body['zip'])    ? trim($body['zip'])    : '';

if (!$priceId || !$email || !$firstName || !$lastName) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address.']);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
\Stripe\Stripe::setApiKey($secret);

try {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base     = $protocol . '://' . $host;

    $params = [
        'mode'       => 'subscription',
        'line_items' => [[
            'price'    => $priceId,
            'quantity' => 1,
        ]],
        'customer_email'  => $email,
        'success_url'     => $base . '/thank-you.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'      => $base . '/index.php',
        'ui_mode'         => 'hosted',
        'metadata'        => [
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ],
    ];

    if ($couponId !== '') {
        $params['discounts'] = [['coupon' => $couponId]];
    }

    $session = \Stripe\Checkout\Session::create($params);

    echo json_encode([
        'url'       => $session->url,
        'sessionId' => $session->id,
    ]);

} catch (\Stripe\Exception\InvalidRequestException $e) {
    http_response_code(422);
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Something went wrong. Please try again.']);
}

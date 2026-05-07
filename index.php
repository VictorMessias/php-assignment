<?php
$products_json = file_get_contents(__DIR__ . '/config/products.json');
$products_data = json_decode($products_json, true);

$prefill = [
    'firstName' => isset($_GET['f']) ? htmlspecialchars($_GET['f'], ENT_QUOTES) : '',
    'lastName'  => isset($_GET['l']) ? htmlspecialchars($_GET['l'], ENT_QUOTES) : '',
    'email'     => isset($_GET['e']) ? htmlspecialchars($_GET['e'], ENT_QUOTES) : '',
];
$hasUrlData = $prefill['firstName'] || $prefill['lastName'] || $prefill['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-wrapper">
    <header class="checkout-header">
        <h1>Choose Your Plan</h1>
        <p>Select the medication and plan that works best for you.</p>
    </header>

    <main class="checkout-main">

        <section class="medication-selector">
            <button class="med-tab active" data-type="injection">Injections</button>
            <button class="med-tab" data-type="tablets">Tablets</button>
        </section>

        <div id="static-banner" style="display:none" class="static-banner">
            Special pricing applied to your session.
        </div>

        <section class="products-grid" id="products-grid">
        </section>

        <section class="plan-options" id="plan-options">
        </section>

        <section class="pricing-summary" id="pricing-summary">
        </section>

        <section class="coupon-section">
            <label for="coupon-input">Have a coupon code?</label>
            <div class="coupon-row">
                <input type="text" id="coupon-input" placeholder="Enter code" autocomplete="off">
                <button type="button" id="coupon-apply">Apply</button>
            </div>
            <div id="coupon-message" class="coupon-message"></div>
        </section>

        <section class="user-form-section">
            <h2>Your Information</h2>
            <form id="user-form" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first-name">First Name</label>
                        <input type="text" id="first-name" name="firstName"
                            value="<?= $prefill['firstName'] ?>"
                            <?= $hasUrlData ? 'readonly' : '' ?>
                            placeholder="Jane">
                        <span class="field-error" id="err-first-name"></span>
                    </div>
                    <div class="form-group">
                        <label for="last-name">Last Name</label>
                        <input type="text" id="last-name" name="lastName"
                            value="<?= $prefill['lastName'] ?>"
                            <?= $hasUrlData ? 'readonly' : '' ?>
                            placeholder="Doe">
                        <span class="field-error" id="err-last-name"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        value="<?= $prefill['email'] ?>"
                        <?= $hasUrlData ? 'readonly' : '' ?>
                        placeholder="jane@example.com">
                    <span class="field-error" id="err-email"></span>
                </div>
                <h3>Shipping Address</h3>
                <div class="form-group">
                    <label for="street">Street</label>
                    <input type="text" id="street" name="street" placeholder="123 Main St">
                    <span class="field-error" id="err-street"></span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" placeholder="New York">
                        <span class="field-error" id="err-city"></span>
                    </div>
                    <div class="form-group form-group--sm">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" placeholder="NY" maxlength="2">
                        <span class="field-error" id="err-state"></span>
                    </div>
                    <div class="form-group form-group--sm">
                        <label for="zip">ZIP</label>
                        <input type="text" id="zip" name="zip" placeholder="10001" maxlength="10">
                        <span class="field-error" id="err-zip"></span>
                    </div>
                </div>
            </form>
        </section>

        <div id="checkout-error" class="checkout-error" style="display:none"></div>

        <button type="button" id="checkout-btn" class="checkout-btn">
            Continue to Payment
        </button>

    </main>
</div>

<script>
    var productsConfig = <?= $products_json ?>;
</script>
<script src="js/checkout.js"></script>
</body>
</html>

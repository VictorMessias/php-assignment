<?php
$products_json = file_get_contents(__DIR__ . '/config/products.json');
$products_data = json_decode($products_json, true);
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

    </main>
</div>

<script>
    var productsConfig = <?= $products_json ?>;
</script>
<script src="js/checkout.js"></script>
</body>
</html>

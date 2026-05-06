var urlParams = new URLSearchParams(window.location.search);
var isStatic = urlParams.get('static') === '1';

var state = {
    medicationType: 'injection',
    selectedProductId: null,
    selectedPlanId: null,
    coupon: null
};

var products = productsConfig.products;

function getProductsByType(type) {
    return products.filter(function(p) {
        return p.medication_type === type;
    });
}

function getProduct(productId) {
    return products.find(function(p) {
        return p.productId === productId;
    });
}

function getPlan(product, planId) {
    return product.plans.find(function(pl) {
        return pl.planId === planId;
    });
}

function getDisplayPrice(plan) {
    if (isStatic && plan.staticPrice !== null) {
        return plan.staticPrice;
    }
    return plan.discountedPrice;
}

function getActiveCoupon(plan) {
    if (isStatic) return plan.static_coupon_id || plan.coupon_id;
    return plan.coupon_id;
}

function renderProducts() {
    var grid = document.getElementById('products-grid');
    grid.innerHTML = '';

    var filtered = getProductsByType(state.medicationType);

    filtered.forEach(function(product) {
        var card = document.createElement('div');
        card.className = 'product-card' + (product.productId === state.selectedProductId ? ' selected' : '');
        card.dataset.productId = product.productId;

        card.innerHTML = '<h3>' + product.productTitle + '</h3>' +
            '<span class="product-subtitle">' + product.medication_type + '</span>' +
            '<span class="card-price">from $' + getDisplayPrice(product.plans[0]) + '</span>';

        card.addEventListener('click', function() {
            state.selectedProductId = product.productId;
            var prod = getProduct(state.selectedProductId);
            state.selectedPlanId = prod.plans[0].planId;
            clearCoupon();
            render();
        });

        grid.appendChild(card);
    });
}

function renderPlans() {
    var container = document.getElementById('plan-options');
    container.innerHTML = '';

    var product = getProduct(state.selectedProductId);
    if (!product) return;

    var title = document.createElement('h2');
    title.textContent = 'Choose a plan';
    container.appendChild(title);

    var list = document.createElement('div');
    list.className = 'plan-list';

    product.plans.forEach(function(plan) {
        var item = document.createElement('div');
        item.className = 'plan-item' + (plan.planId === state.selectedPlanId ? ' selected' : '');
        item.dataset.planId = plan.planId;

        item.innerHTML = '<span class="plan-label">' + plan.label + '</span>' +
            '<div class="plan-price">' +
                '<div class="price-original">$' + plan.totalPrice + '</div>' +
                '<div class="price-current">$' + getDisplayPrice(plan) + '</div>' +
            '</div>';

        item.addEventListener('click', function() {
            state.selectedPlanId = plan.planId;
            clearCoupon();
            render();
        });

        list.appendChild(item);
    });

    container.appendChild(list);
}

function renderSummary() {
    var container = document.getElementById('pricing-summary');
    container.innerHTML = '';

    var product = getProduct(state.selectedProductId);
    if (!product) return;

    var plan = getPlan(product, state.selectedPlanId);
    if (!plan) return;

    var displayPrice = getDisplayPrice(plan);
    var finalPrice = displayPrice;

    if (state.coupon) {
        if (state.coupon.amountOff) {
            finalPrice = Math.max(0, displayPrice - state.coupon.amountOff);
        } else if (state.coupon.percentOff) {
            finalPrice = Math.max(0, displayPrice - (displayPrice * state.coupon.percentOff / 100));
        }
        finalPrice = Math.round(finalPrice * 100) / 100;
    }

    var savings = plan.totalPrice - displayPrice;

    var rows = [
        { label: 'Product', value: product.productTitle },
        { label: 'Plan', value: plan.label },
        { label: 'Regular Price', value: '$' + plan.totalPrice },
        { label: 'You save', value: '-$' + savings, isSavings: true }
    ];

    if (state.coupon) {
        rows.push({ label: 'Coupon (' + state.coupon.couponId + ')', value: '-' + state.coupon.label, isSavings: true });
    }

    rows.push({ label: 'Today\'s Price', value: '$' + finalPrice, isTotal: true });

    rows.forEach(function(row) {
        var div = document.createElement('div');
        div.className = 'summary-row' + (row.isTotal ? ' total' : '');
        var valueClass = row.isSavings ? ' class="value savings"' : ' class="value"';
        div.innerHTML = '<span class="label">' + row.label + '</span><span' + valueClass + '>' + row.value + '</span>';
        container.appendChild(div);
    });
}

function render() {
    renderProducts();
    renderPlans();
    renderSummary();
}

function initMedTabs() {
    document.querySelectorAll('.med-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.med-tab').forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');

            state.medicationType = tab.dataset.type;

            var available = getProductsByType(state.medicationType);
            if (available.length > 0) {
                state.selectedProductId = available[0].productId;
                state.selectedPlanId = available[0].plans[0].planId;
            }

            render();
        });
    });
}

function clearCoupon() {
    state.coupon = null;
    var input = document.getElementById('coupon-input');
    var msg = document.getElementById('coupon-message');
    if (input) input.value = '';
    if (msg) { msg.textContent = ''; msg.className = 'coupon-message'; }
}

function applyCoupon() {
    var input = document.getElementById('coupon-input');
    var msg = document.getElementById('coupon-message');
    var code = input ? input.value.trim() : '';

    if (!code) {
        msg.textContent = 'Please enter a coupon code.';
        msg.className = 'coupon-message error';
        return;
    }

    var product = getProduct(state.selectedProductId);
    var plan = product ? getPlan(product, state.selectedPlanId) : null;
    if (!plan) return;

    var btn = document.getElementById('coupon-apply');
    btn.disabled = true;
    btn.textContent = 'Checking...';
    msg.textContent = '';
    msg.className = 'coupon-message';

    fetch('ajax/validate-coupon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: code, priceId: plan.priceId })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.textContent = 'Apply';

        if (data.error) {
            msg.textContent = data.error;
            msg.className = 'coupon-message error';
            return;
        }

        if (!data.valid) {
            state.coupon = null;
            msg.textContent = data.message;
            msg.className = 'coupon-message error';
        } else {
            state.coupon = data;
            msg.textContent = data.message;
            msg.className = 'coupon-message success';
            renderSummary();
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = 'Apply';
        msg.textContent = 'Network error, please try again.';
        msg.className = 'coupon-message error';
    });
}

function initCoupon() {
    var btn = document.getElementById('coupon-apply');
    var input = document.getElementById('coupon-input');
    if (btn) btn.addEventListener('click', applyCoupon);
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') applyCoupon();
        });
    }
}

function init() {
    var injections = getProductsByType('injection');
    if (injections.length > 0) {
        state.selectedProductId = injections[0].productId;
        state.selectedPlanId = injections[0].plans[0].planId;
    }

    if (isStatic) {
        var banner = document.getElementById('static-banner');
        if (banner) banner.style.display = 'block';
    }

    initMedTabs();
    initCoupon();
    render();
}

init();

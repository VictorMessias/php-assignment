var state = {
    medicationType: 'injection',
    selectedProductId: null,
    selectedPlanId: null
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

function renderProducts() {
    var grid = document.getElementById('products-grid');
    grid.innerHTML = '';

    var filtered = getProductsByType(state.medicationType);

    filtered.forEach(function(product) {
        var card = document.createElement('div');
        card.className = 'product-card' + (product.productId === state.selectedProductId ? ' selected' : '');
        card.dataset.productId = product.productId;

        card.innerHTML = '<h3>' + product.productTitle + '</h3>' +
            '<span class="product-subtitle">' + product.medication_type + '</span>';

        card.addEventListener('click', function() {
            state.selectedProductId = product.productId;
            var prod = getProduct(state.selectedProductId);
            state.selectedPlanId = prod.plans[0].planId;
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
                '<div class="price-current">$' + plan.discountedPrice + '</div>' +
            '</div>';

        item.addEventListener('click', function() {
            state.selectedPlanId = plan.planId;
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

    var rows = [
        { label: 'Product', value: product.productTitle },
        { label: 'Plan', value: plan.label },
        { label: 'Regular Price', value: '$' + plan.totalPrice },
        { label: 'Today\'s Price', value: '$' + plan.discountedPrice, isTotal: true }
    ];

    rows.forEach(function(row) {
        var div = document.createElement('div');
        div.className = 'summary-row' + (row.isTotal ? ' total' : '');
        div.innerHTML = '<span class="label">' + row.label + '</span><span class="value">' + row.value + '</span>';
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

function init() {
    var injections = getProductsByType('injection');
    if (injections.length > 0) {
        state.selectedProductId = injections[0].productId;
        state.selectedPlanId = injections[0].plans[0].planId;
    }

    initMedTabs();
    render();
}

init();

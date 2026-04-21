<?php
declare(strict_types=1);
session_start();

/* Must be logged in to checkout */
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['flash_msg']  = 'Please log in to checkout.';
    $_SESSION['flash_type'] = 'err';
    header("Location: /PHP_Form_Customer/customer_login_form.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$user_id = (int)$_SESSION['customer_id'];

/* Load basket from DB */
$stmt = $conn->prepare("
    SELECT
        p.product_id, p.name AS product_name, p.price, p.image, p.unit,
        pr.farm_name,
        b.quantity,
        i.stock_qty
    FROM basket b
    JOIN products p ON p.product_id = b.product_id
    JOIN inventory i ON i.product_id = p.product_id
    JOIN producers pr ON pr.producer_id = p.producer_id
    WHERE b.user_id = ? AND p.is_active = 1 AND i.stock_qty > 0
    ORDER BY b.added_at
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($products)) {
    $conn->close();
    header("Location: /PHP_Form_Customer/customer_view_basket_form.php");
    exit();
}

$totalCost  = 0.0;
$totalItems = 0;
foreach ($products as &$p) {
    $p['line_total'] = $p['quantity'] * (float)$p['price'];
    $totalCost  += $p['line_total'];
    $totalItems += $p['quantity'];
}
unset($p);

/* Loyalty points balance */
$stmt = $conn->prepare("SELECT COALESCE(points_balance,0) FROM loyalty_accounts WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($loyaltyBalance);
$stmt->fetch();
$stmt->close();
$conn->close();

/* Max loyalty discount: 10% of order, 1 pt = £0.01 */
$maxLoyaltyDiscount = round(min($loyaltyBalance * 0.01, $totalCost * 0.10), 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <script src="/JavaScripts/accessibility.js" defer></script>
    <style>
        body { background:#f5f5f0; }
        .checkout-wrap {
            max-width:900px; margin:100px auto 60px; padding:0 20px;
            display:grid; grid-template-columns:1fr 380px; gap:30px;
        }
        @media(max-width:740px){ .checkout-wrap{ grid-template-columns:1fr; } }
        .checkout-section {
            background:#fff; border:2px solid #e5e7eb; border-radius:14px; padding:24px;
        }
        .checkout-section h2 { font-size:1.1rem; font-weight:800; margin-bottom:18px; color:#134e4a; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-weight:600; margin-bottom:4px; font-size:0.9rem; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:10px 12px; border-radius:8px;
            border:1px solid #aaa; font-size:0.95rem; box-sizing:border-box;
        }
        .fulfilment-options { display:flex; gap:14px; margin-bottom:16px; }
        .fulfilment-option {
            flex:1; border:2px solid #ddd; border-radius:10px; padding:14px;
            cursor:pointer; text-align:center; transition:0.2s;
        }
        .fulfilment-option.selected { border-color:#c6d083; background:#f0f5dc; }
        .fulfilment-option h4 { font-weight:700; margin-bottom:4px; }
        .order-line { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee; font-size:0.9rem; }
        .order-total { display:flex; justify-content:space-between; padding:12px 0 0; font-weight:800; font-size:1.05rem; }
        .loyalty-row { margin:12px 0; padding:12px; background:#fef9c3; border-radius:8px; font-size:0.9rem; }
        .place-btn {
            width:100%; padding:14px; border-radius:30px; background:#c6d083;
            border:2px solid #c6d083; font-weight:800; font-size:1rem;
            cursor:pointer; margin-top:18px; transition:0.2s;
        }
        .place-btn:hover { background:#b8c870; transform:translateY(-2px); }
        #delivery_address_block { display:none; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<section class="hero login-hero">
    <div class="hero-content">
        <h1>Checkout</h1>
        <p>Almost there &mdash; confirm your order below</p>
    </div>
</section>

<form action="/PHP_Scripts_Customer/customer_checkout.php" method="POST">
<div class="checkout-wrap">

    <!-- LEFT: Delivery details -->
    <div>
        <div class="checkout-section" style="margin-bottom:20px;">
            <h2>1. Fulfilment Method</h2>
            <div class="fulfilment-options">
                <label class="fulfilment-option selected" id="label_collection">
                    <input type="radio" name="fulfilment_type" value="collection" checked style="display:none;" id="radio_collection">
                    <h4>&#127981; Collection</h4>
                    <p style="font-size:0.8rem;color:#555;">Pick up from Greenfield Local Hub</p>
                </label>
                <label class="fulfilment-option" id="label_delivery">
                    <input type="radio" name="fulfilment_type" value="delivery" style="display:none;" id="radio_delivery">
                    <h4>&#128666; Delivery</h4>
                    <p style="font-size:0.8rem;color:#555;">Delivered to your address (+£2.99)</p>
                </label>
            </div>

            <div id="delivery_address_block">
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="delivery_address" rows="3" placeholder="Enter your full delivery address"></textarea>
                </div>
            </div>
        </div>

        <div class="checkout-section">
            <h2>2. Contact Details</h2>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="contact_name" value="<?= htmlspecialchars($_SESSION['customer_username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="contact_phone" placeholder="e.g. 07700 900123" required>
            </div>
            <div class="form-group">
                <label>Order Notes (optional)</label>
                <textarea name="order_notes" rows="2" placeholder="Any special requests?"></textarea>
            </div>
        </div>
    </div>

    <!-- RIGHT: Order summary -->
    <div class="checkout-section" style="height:fit-content;">
        <h2>Order Summary</h2>

        <?php foreach ($products as $p): ?>
            <div class="order-line">
                <span><?= htmlspecialchars($p['product_name']) ?> &times;<?= $p['quantity'] ?></span>
                <span>&pound;<?= number_format($p['line_total'], 2) ?></span>
            </div>
        <?php endforeach; ?>

        <?php if ($loyaltyBalance > 0): ?>
        <div class="loyalty-row">
            <strong>&#127775; Loyalty Points:</strong> <?= $loyaltyBalance ?> pts available<br>
            <label style="display:flex;align-items:center;gap:8px;margin-top:6px;">
                <input type="checkbox" name="use_loyalty" value="1" id="use_loyalty">
                Use points (save up to &pound;<?= number_format($maxLoyaltyDiscount, 2) ?>)
            </label>
            <input type="hidden" name="loyalty_discount" value="<?= $maxLoyaltyDiscount ?>">
        </div>
        <?php endif; ?>

        <div class="order-line" id="delivery_fee_row" style="display:none;">
            <span>Delivery Fee</span><span>&pound;2.99</span>
        </div>

        <div class="order-total">
            <span>Total</span>
            <span id="order_total">&pound;<?= number_format($totalCost, 2) ?></span>
        </div>

        <input type="hidden" name="base_total" value="<?= number_format($totalCost, 2, '.', '') ?>">
        <button type="submit" class="place-btn">&#10003; Place Order</button>
    </div>

</div>
</form>

<script>
const radioCollection = document.getElementById('radio_collection');
const radioDelivery   = document.getElementById('radio_delivery');
const labelCollection = document.getElementById('label_collection');
const labelDelivery   = document.getElementById('label_delivery');
const addrBlock       = document.getElementById('delivery_address_block');
const deliveryRow     = document.getElementById('delivery_fee_row');
const totalEl         = document.getElementById('order_total');
const useLoyalty      = document.getElementById('use_loyalty');
const baseTotal       = parseFloat(document.querySelector('[name="base_total"]').value);
const loyaltyDiscount = <?= $maxLoyaltyDiscount ?>;

function recalcTotal() {
    let total = baseTotal;
    if (radioDelivery.checked) total += 2.99;
    if (useLoyalty && useLoyalty.checked) total -= loyaltyDiscount;
    if (total < 0) total = 0;
    totalEl.textContent = '£' + total.toFixed(2);
}

[radioCollection, radioDelivery].forEach(r => r.addEventListener('change', () => {
    const isDelivery = radioDelivery.checked;
    labelDelivery.classList.toggle('selected', isDelivery);
    labelCollection.classList.toggle('selected', !isDelivery);
    addrBlock.style.display   = isDelivery ? 'block' : 'none';
    deliveryRow.style.display = isDelivery ? 'flex' : 'none';
    recalcTotal();
}));

if (useLoyalty) useLoyalty.addEventListener('change', recalcTotal);

document.querySelectorAll('.fulfilment-option').forEach(el => {
    el.addEventListener('click', () => {
        const input = el.querySelector('input[type="radio"]');
        if (input) { input.checked = true; input.dispatchEvent(new Event('change')); }
    });
});
</script>
</body>
</html>

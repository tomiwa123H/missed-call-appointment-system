<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$products   = [];
$totalCost  = 0.0;
$totalItems = 0;

if (isset($_SESSION['customer_id'])) {
    /* Logged-in: load basket from DB */
    $user_id = (int)$_SESSION['customer_id'];
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
        ORDER BY b.added_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($rows as $row) {
        $row['line_total'] = $row['quantity'] * (float)$row['price'];
        $totalCost  += $row['line_total'];
        $totalItems += $row['quantity'];
        $products[]  = $row;
    }
} else {
    /* Guest: session basket */
    $basket = $_SESSION['basket'] ?? [];
    if (!empty($basket)) {
        $ids = implode(',', array_map('intval', array_keys($basket)));
        $result = $conn->query("
            SELECT p.product_id, p.name AS product_name, p.price, p.image, p.unit,
                   i.stock_qty, pr.farm_name
            FROM products p
            JOIN inventory i ON i.product_id = p.product_id
            JOIN producers pr ON pr.producer_id = p.producer_id
            WHERE p.product_id IN ($ids) AND p.is_active = 1 AND i.stock_qty > 0
        ");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pid = (int)$row['product_id'];
                $qty = (int)($basket[$pid] ?? 0);
                if ($qty > 0) {
                    $row['quantity']   = $qty;
                    $row['line_total'] = $qty * (float)$row['price'];
                    $totalCost  += $row['line_total'];
                    $totalItems += $qty;
                    $products[]  = $row;
                }
            }
        }
    }
}

$loyaltyPoints = (int)floor($totalCost);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenfield Hub | Basket</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/basket.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <script src="/JavaScripts/accessibility.js" defer></script>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<section class="hero login-hero">
    <div class="hero-content">
        <h1>Your Basket</h1>
        <p>Review your items before checkout</p>
    </div>
</section>

<?php if (!empty($_SESSION['flash_msg'])): ?>
    <div style="max-width:800px;margin:16px auto;padding:12px 16px;border-radius:10px;background:<?= ($_SESSION['flash_type'] ?? 'ok') === 'err' ? '#fee2e2' : '#dcfce7' ?>;">
        <?= htmlspecialchars($_SESSION['flash_msg']) ?>
    </div>
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<section class="basket-wrap">
    <form method="POST" action="/PHP_Scripts_Customer/customer_update_basket.php">

        <div class="product-list">

            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($product['product_name']) ?></div>
                            <div class="product-meta">Producer: <?= htmlspecialchars($product['farm_name'] ?? 'Unknown') ?></div>
                            <div class="product-meta">Price: &pound;<?= number_format((float)$product['price'], 2) ?> <?= htmlspecialchars($product['unit'] ?? '') ?></div>
                            <div class="product-meta">Line Total: &pound;<?= number_format((float)$product['line_total'], 2) ?></div>
                        </div>
                        <div class="product-actions">
                            <?php if (!empty($product['image'])): ?>
                                <img src="/uploads/<?= htmlspecialchars($product['image']) ?>"
                                     class="product-image" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            <?php endif; ?>
                            <input type="number"
                                   name="qty[<?= (int)$product['product_id'] ?>]"
                                   value="<?= (int)$product['quantity'] ?>"
                                   min="0"
                                   max="<?= (int)$product['stock_qty'] ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-basket">
                    <p>Your basket is empty.</p>
                    <a href="/PHP_Form/products.php" style="color:#16a34a;font-weight:600;">Browse Products</a>
                </div>
            <?php endif; ?>

        </div>

        <?php if (!empty($products)): ?>
        <div class="basket-summary">
            <div class="summary-row"><span>Total Items</span><span><?= $totalItems ?></span></div>
            <div class="summary-row"><span>Total Cost</span><span>&pound;<?= number_format($totalCost, 2) ?></span></div>
            <div class="summary-row"><span>Loyalty Points Earned</span><span><?= $loyaltyPoints ?> pts</span></div>
        </div>

        <div class="action-bar">
            <button type="submit" name="update_basket" class="btn">Update Basket</button>
            <a href="/PHP_Form/products.php" class="btn" style="background:#ddd;color:#000;text-decoration:none;">Continue Shopping</a>
            <a href="/PHP_Form_Customer/checkout_form.php" class="btn" style="background:#16a34a;">Checkout</a>
        </div>
        <?php endif; ?>

    </form>
</section>
</body>
</html>

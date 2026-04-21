<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['customer_id']) || !isset($_SESSION['order_confirmation'])) {
    header("Location: /index.php");
    exit();
}

$conf = $_SESSION['order_confirmation'];
unset($_SESSION['order_confirmation']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <script src="/JavaScripts/accessibility.js" defer></script>
    <style>
        .confirm-wrap {
            max-width:600px; margin:110px auto 60px; padding:0 20px;
        }
        .confirm-card {
            background:#fff; border:2px solid #c6d083; border-radius:18px;
            padding:40px; text-align:center;
            box-shadow:0 8px 24px rgba(0,0,0,0.08);
        }
        .confirm-icon { font-size:72px; margin-bottom:16px; }
        .confirm-card h1 { font-size:1.8rem; font-weight:800; color:#134e4a; margin-bottom:10px; }
        .confirm-card p  { color:#555; margin-bottom:6px; font-size:1rem; }
        .order-details {
            background:#f0f5dc; border-radius:10px; padding:20px;
            margin:20px 0; text-align:left;
        }
        .order-details .row {
            display:flex; justify-content:space-between;
            padding:6px 0; border-bottom:1px solid #dde5b2; font-size:0.95rem;
        }
        .order-details .row:last-child { border-bottom:none; }
        .points-badge {
            display:inline-block; background:#c6d083; border-radius:30px;
            padding:8px 20px; font-weight:700; font-size:0.95rem; margin:12px 0;
        }
        .action-buttons { display:flex; gap:14px; justify-content:center; margin-top:24px; flex-wrap:wrap; }
        .btn-primary {
            padding:12px 24px; border-radius:30px; background:#c6d083;
            border:2px solid #c6d083; font-weight:700; cursor:pointer;
            text-decoration:none; color:#000; transition:0.2s;
        }
        .btn-primary:hover { background:#b8c870; transform:translateY(-2px); }
        .btn-secondary {
            padding:12px 24px; border-radius:30px; background:#ddd;
            border:2px solid #000; font-weight:600; cursor:pointer;
            text-decoration:none; color:#000; transition:0.2s;
        }
        .btn-secondary:hover { background:#c6c6c6; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<div class="confirm-wrap">
    <div class="confirm-card">
        <div class="confirm-icon">&#10003;</div>
        <h1>Order Placed!</h1>
        <p>Thank you for shopping with Greenfield Local Hub.</p>
        <p>Your order has been received and is being prepared.</p>

        <div class="order-details">
            <div class="row">
                <span><strong>Order ID</strong></span>
                <span>#<?= (int)$conf['order_id'] ?></span>
            </div>
            <div class="row">
                <span><strong>Fulfilment</strong></span>
                <span><?= ucfirst(htmlspecialchars($conf['fulfilment'])) ?></span>
            </div>
            <div class="row">
                <span><strong>Order Total</strong></span>
                <span>&pound;<?= number_format((float)$conf['total'], 2) ?></span>
            </div>
            <div class="row">
                <span><strong>Status</strong></span>
                <span style="color:#16a34a;font-weight:700;">Placed</span>
            </div>
        </div>

        <?php if ($conf['points_earned'] > 0): ?>
            <div class="points-badge">
                &#127775; You earned <?= (int)$conf['points_earned'] ?> loyalty points!
            </div>
        <?php endif; ?>

        <?php if ($conf['fulfilment'] === 'collection'): ?>
            <p style="font-size:0.9rem;color:#555;margin-top:8px;">
                You will receive a notification when your order is ready for collection.
            </p>
        <?php else: ?>
            <p style="font-size:0.9rem;color:#555;margin-top:8px;">
                Your order will be delivered to your address. We will notify you when it is on the way.
            </p>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="/PHP_Form_Customer/customer_orders.php" class="btn-primary">View My Orders</a>
            <a href="/PHP_Form/products.php" class="btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>
</body>
</html>

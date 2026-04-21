<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: /PHP_Form_Customer/customer_login_form.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$user_id = (int)$_SESSION['customer_id'];

/* Fetch orders with item summary */
$stmt = $conn->prepare("
    SELECT
        o.order_id, o.status, o.fulfilment_type, o.total, o.created_at,
        GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ', ') AS product_names,
        SUM(oi.quantity) AS total_items
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.order_id
    JOIN products p ON p.product_id = oi.product_id
    WHERE o.customer = ?
    GROUP BY o.order_id, o.status, o.fulfilment_type, o.total, o.created_at
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$statusColours = [
    'placed'    => '#fef9c3',
    'preparing' => '#fed7aa',
    'ready'     => '#bbf7d0',
    'completed' => '#d1fae5',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <script src="/JavaScripts/accessibility.js" defer></script>
    <style>
        .order-card {
            background:#fff; border:2px solid #e5e7eb; border-radius:14px;
            padding:20px; margin-bottom:16px;
        }
        .order-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
        .order-id { font-weight:800; color:#134e4a; font-size:1rem; }
        .status-badge { padding:5px 14px; border-radius:20px; font-weight:700; font-size:0.82rem; text-transform:uppercase; }
        .order-meta { margin-top:10px; font-size:0.9rem; color:#555; line-height:1.8; }
        .orders-container { max-width:800px; width:100%; padding:0 20px 60px; }
        .no-orders { text-align:center; padding:40px; background:#fff; border-radius:14px; border:2px dashed #ddd; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<?php
/* Sidebar for customer dashboard */
$current_page = 'orders';
?>
<button id="openSidebar" class="open-btn">&#9776;</button>
<div id="sidebar" class="sidebar active">
    <button id="closeSidebar" class="close-btn">&times;</button>
    <div class="menu">
        <a href="/PHP_Form_Customer/customer_dashboard_form.php" class="menu-btn">Overview</a>
        <a href="/PHP_Form_Customer/customer_orders.php" class="menu-btn active">My Orders</a>
        <a href="/PHP_Form_Customer/customer_account_settings.php" class="menu-btn">Account Settings</a>
    </div>
    <form action="/php_script_auth/customer_logout.php" method="post">
        <button type="submit" class="logout-btn">Log Out</button>
    </form>
</div>

<div class="dashboard-container">
    <div class="dashboard-header"><h1>My Orders</h1></div>

    <div class="orders-container">
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <p>You have not placed any orders yet.</p>
                <a href="/PHP_Form/products.php" style="color:#16a34a;font-weight:600;">Browse Products</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">Order #<?= (int)$order['order_id'] ?></span>
                        <span class="status-badge" style="background:<?= $statusColours[$order['status']] ?? '#e5e7eb' ?>;">
                            <?= ucfirst(htmlspecialchars($order['status'])) ?>
                        </span>
                    </div>
                    <div class="order-meta">
                        <strong>Placed:</strong> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?><br>
                        <strong>Fulfilment:</strong> <?= ucfirst(htmlspecialchars($order['fulfilment_type'])) ?><br>
                        <strong>Total:</strong> &pound;<?= number_format((float)$order['total'], 2) ?><br>
                        <strong>Items:</strong> <?= htmlspecialchars($order['product_names'] ?? '') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const sidebar = document.getElementById("sidebar");
document.getElementById("openSidebar").onclick = () => sidebar.classList.add("active");
document.getElementById("closeSidebar").onclick = () => sidebar.classList.remove("active");
</script>
</body>
</html>

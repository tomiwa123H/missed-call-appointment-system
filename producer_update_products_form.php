<?php
declare(strict_types=1);
session_start();

/* PRODUCER ACCESS CONTROL */
if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'producer' ||
    !isset($_SESSION['producer_id'])
) {
    header("Location: /PHP_Form/login_choice_form.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$producer_id   = (int)$_SESSION['producer_id'];
$producer_name = $_SESSION['producer_email'] ?? 'Producer';

/* Orders Today */
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id)
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.order_id
    JOIN products p ON p.product_id = oi.product_id
    WHERE p.producer_id = ?
      AND DATE(o.created_at) = CURDATE()
");
$stmt->bind_param("i", $producer_id);
$stmt->execute();
$stmt->bind_result($ordersToday);
$stmt->fetch();
$stmt->close();

/* Low Stock Alerts */
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM inventory i
    JOIN products p ON p.product_id = i.product_id
    WHERE p.producer_id = ?
      AND i.stock_qty <= i.low_stock_threshold
");
$stmt->bind_param("i", $producer_id);
$stmt->execute();
$stmt->bind_result($lowStock);
$stmt->fetch();
$stmt->close();

/* Pending Orders */
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id)
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.order_id
    JOIN products p ON p.product_id = oi.product_id
    WHERE p.producer_id = ?
      AND o.status IN ('placed','preparing')
");
$stmt->bind_param("i", $producer_id);
$stmt->execute();
$stmt->bind_result($pendingOrders);
$stmt->fetch();
$stmt->close();

/* Weekly Revenue */
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(oi.line_total), 0)
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.order_id
    JOIN products p ON p.product_id = oi.product_id
    WHERE p.producer_id = ?
      AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$stmt->bind_param("i", $producer_id);
$stmt->execute();
$stmt->bind_result($weeklyRevenue);
$stmt->fetch();
$stmt->close();

/* Total Products */
$stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE producer_id = ? AND is_active = 1");
$stmt->bind_param("i", $producer_id);
$stmt->execute();
$stmt->bind_result($totalProducts);
$stmt->fetch();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producer Dashboard | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/producer_dashboard.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">
    <script src="/JavaScripts/producer_dashboaerd.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/../components/navigation.php'; ?>
<?php include __DIR__ . '/../components/producer_sidebar.php'; ?>

<div class="dashboard-container">

    <div class="dashboard-header">
        <h1>Welcome, <?= htmlspecialchars($producer_name) ?></h1>
        <p style="color:#555;margin-top:4px;">Greenfield Local Hub &mdash; Producer Dashboard</p>
    </div>

    <div class="grid">

        <a href="/PHP_Form_Staff/producer_orders_list_form.php" class="card">
            <h3>Orders Today</h3>
            <p class="card-number"><?= (int)$ordersToday ?></p>
        </a>

        <a href="/PHP_Form_Staff/producer_products_list_form.php" class="card">
            <h3>Low Stock Alerts</h3>
            <p class="card-number" style="<?= (int)$lowStock > 0 ? 'color:#b91c1c;' : '' ?>"><?= (int)$lowStock ?></p>
        </a>

        <a href="/PHP_Form_Staff/producer_orders_list_form.php" class="card">
            <h3>Pending Orders</h3>
            <p class="card-number"><?= (int)$pendingOrders ?></p>
        </a>

        <div class="card">
            <h3>Revenue (This Week)</h3>
            <p class="card-number" style="font-size:36px;">
                &pound;<?= number_format((float)$weeklyRevenue, 2) ?>
            </p>
        </div>

        <a href="/PHP_Form_Staff/producer_products_list_form.php" class="card">
            <h3>Active Products</h3>
            <p class="card-number"><?= (int)$totalProducts ?></p>
        </a>

        <a href="/PHP_Form_Staff/producer_add_product_form.php" class="card" style="background:#b8c870;">
            <h3>+ Add New Product</h3>
            <p style="font-size:0.9rem;margin-top:8px;color:#333;">List a new item for customers</p>
        </a>

    </div>

</div>

</body>
</html>

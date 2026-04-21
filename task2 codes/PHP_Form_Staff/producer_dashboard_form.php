<?php
declare(strict_types=1);
session_start();

/* ✅ PRODUCER ACCESS CONTROL */
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

$producer_id   = $_SESSION['producer_id'];
$producer_name = $_SESSION['producer_email']; // producer email as display name

/* ---------------- DASHBOARD METRICS ---------------- */

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Producer Dashboard | Greenfield Hub</title>

    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">

    <script src="/JavaScripts/customer_dashboard.js" defer></script>
</head>

<body>

<?php include __DIR__ . '/../components/navigation.php'; ?>
<?php include __DIR__ . '/../components/producer_sidebar.php'; ?>

<!-- DASHBOARD -->
<div class="dashboard-container">

    <div class="dashboard-header">
        <h1>Welcome, <?= htmlspecialchars($producer_name) ?></h1>
    </div>

    <div class="grid">

        <div class="card">
            <h3>Orders Today</h3>
            <p class="card-number"><?= (int)$ordersToday ?></p>
        </div>

        <div class="card">
            <h3>Low Stock Alerts</h3>
            <p class="card-number"><?= (int)$lowStock ?></p>
        </div>

        <div class="card">
            <h3>Pending Orders</h3>
            <p class="card-number"><?= (int)$pendingOrders ?></p>
        </div>

        <div class="card">
            <h3>Revenue Overview (Weekly)</h3>
            <p class="card-number">
                £<?= number_format((float)$weeklyRevenue, 2) ?>
            </p>
        </div>

    </div>

</div>

</body>
</html>

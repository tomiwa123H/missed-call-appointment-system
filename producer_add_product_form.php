<?php
declare(strict_types=1);
session_start();

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

$producer_id = (int)$_SESSION['producer_id'];

/* Filter by status if provided */
$status_filter = trim($_GET['status'] ?? '');
$allowed_statuses = ['placed', 'preparing', 'ready', 'completed'];

$where_status = '';
$bind_types   = 'i';
$bind_params  = [$producer_id];

if ($status_filter !== '' && in_array($status_filter, $allowed_statuses, true)) {
    $where_status = "AND o.status = ?";
    $bind_types  .= 's';
    $bind_params[] = $status_filter;
}

$sql = "
    SELECT
        o.order_id,
        o.status,
        o.fulfilment_type,
        o.created_at,
        o.total,
        u.name AS customer_name,
        u.email AS customer_email,
        GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ', ') AS product_names,
        SUM(oi.quantity) AS total_items
    FROM orders o
    JOIN users u ON u.user_id = o.customer
    JOIN order_items oi ON oi.order_id = o.order_id
    JOIN products p ON p.product_id = oi.product_id
    WHERE p.producer_id = ?
    $where_status
    GROUP BY o.order_id, o.status, o.fulfilment_type, o.created_at, o.total, u.name, u.email
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($bind_types, ...$bind_params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$statusColours = [
    'placed'    => '#fef9c3',
    'preparing' => '#fde68a',
    'ready'     => '#bbf7d0',
    'completed' => '#d1fae5',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/producer_dashboard.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">
    <style>
        .orders-wrap { width:100%; max-width:920px; padding:0 20px 40px; }
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:24px; }
        .filter-btn {
            padding:8px 18px; border-radius:30px; border:2px solid #000;
            background:#ddd; font-weight:600; cursor:pointer; text-decoration:none;
            color:#1d1d1d; font-size:0.9rem; transition:0.2s;
        }
        .filter-btn:hover, .filter-btn.active { background:#c6d083; border-color:#c6d083; }
        .order-card {
            background:#fff; border:2px solid #e5e7eb; border-radius:14px;
            padding:20px; margin-bottom:18px;
        }
        .order-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
        .order-id { font-weight:800; font-size:1.05rem; color:#134e4a; }
        .status-badge {
            padding:5px 14px; border-radius:20px; font-weight:700;
            font-size:0.85rem; text-transform:uppercase;
        }
        .order-meta { margin-top:10px; font-size:0.9rem; color:#555; line-height:1.8; }
        .order-products { margin-top:8px; font-size:0.9rem; color:#333; }
        .update-form { margin-top:14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .update-select {
            padding:8px 12px; border-radius:8px; border:1px solid #aaa;
            font-size:0.9rem; background:#f9f9f9;
        }
        .update-btn {
            padding:8px 20px; border-radius:30px; background:#c6d083;
            border:2px solid #c6d083; font-weight:700; cursor:pointer;
            font-size:0.9rem; transition:0.2s;
        }
        .update-btn:hover { background:#b8c870; transform:translateY(-2px); }
        .no-orders { text-align:center; padding:40px; background:#fff; border-radius:14px; border:2px dashed #ddd; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>
<?php include __DIR__ . '/../components/producer_sidebar.php'; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Orders</h1>
    </div>

    <div class="orders-wrap">

        <?php if (!empty($_SESSION['flash_msg'])): ?>
            <div style="padding:12px 16px;border-radius:10px;margin-bottom:16px;background:<?= ($_SESSION['flash_type'] ?? 'ok') === 'err' ? '#fee2e2' : '#dcfce7' ?>;">
                <?= htmlspecialchars($_SESSION['flash_msg']) ?>
            </div>
            <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- Filter bar -->
        <div class="filter-bar">
            <a href="/PHP_Form_Staff/producer_orders_list_form.php" class="filter-btn <?= $status_filter === '' ? 'active' : '' ?>">All</a>
            <a href="?status=placed"    class="filter-btn <?= $status_filter === 'placed'    ? 'active' : '' ?>">Placed</a>
            <a href="?status=preparing" class="filter-btn <?= $status_filter === 'preparing' ? 'active' : '' ?>">Preparing</a>
            <a href="?status=ready"     class="filter-btn <?= $status_filter === 'ready'     ? 'active' : '' ?>">Ready</a>
            <a href="?status=completed" class="filter-btn <?= $status_filter === 'completed' ? 'active' : '' ?>">Completed</a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <p>No orders found<?= $status_filter ? " with status <strong>$status_filter</strong>" : '' ?>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">Order #<?= (int)$order['order_id'] ?></span>
                        <span class="status-badge"
                              style="background:<?= $statusColours[$order['status']] ?? '#e5e7eb' ?>;">
                            <?= htmlspecialchars(ucfirst($order['status'])) ?>
                        </span>
                    </div>
                    <div class="order-meta">
                        <strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?> &mdash; <?= htmlspecialchars($order['customer_email']) ?><br>
                        <strong>Fulfilment:</strong> <?= ucfirst(htmlspecialchars($order['fulfilment_type'])) ?><br>
                        <strong>Placed:</strong> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?><br>
                        <strong>Order Total:</strong> &pound;<?= number_format((float)$order['total'], 2) ?> &mdash;
                        <strong>Items:</strong> <?= (int)$order['total_items'] ?>
                    </div>
                    <div class="order-products">
                        <strong>Products:</strong> <?= htmlspecialchars($order['product_names']) ?>
                    </div>
                    <!-- Update status -->
                    <form class="update-form" action="/PHP_Scripts_producer/producer_update_order_status.php" method="POST">
                        <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
                        <select name="status" class="update-select">
                            <?php foreach ($allowed_statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="update-btn">Update Status</button>
                    </form>
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

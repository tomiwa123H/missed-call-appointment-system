<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: /PHP_Form/login_choice_form.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$user_id  = (int)$_SESSION['customer_id'];
$username = $_SESSION['customer_username'] ?? 'Customer';

/* Basket count from DB */
$stmt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) FROM basket WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($basketCount);
$stmt->fetch();
$stmt->close();

/* Order count */
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($orderCount);
$stmt->fetch();
$stmt->close();

/* Loyalty points */
$stmt = $conn->prepare("SELECT COALESCE(points_balance,0) FROM loyalty_accounts WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($loyaltyPoints);
$stmt->fetch();
$stmt->close();

/* Active orders (not completed) */
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer=? AND status NOT IN ('completed')");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($activeOrders);
$stmt->fetch();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">
    <script src="/JavaScripts/accessibility.js" defer></script>
    <script src="/JavaScripts/customer_dashboard.js" defer></script>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<button id="openSidebar" class="open-btn">&#9776;</button>

<div id="sidebar" class="sidebar active">
    <button id="closeSidebar" class="close-btn">&times;</button>
    <div class="menu">
        <a href="/PHP_Form_Customer/customer_dashboard_form.php" class="menu-btn active">Overview</a>
        <a href="/PHP_Form_Customer/customer_orders.php" class="menu-btn">My Orders</a>
        <a href="/PHP_Form_Customer/customer_account_settings.php" class="menu-btn">Account Settings</a>
    </div>
    <form action="/php_script_auth/customer_logout.php" method="post">
        <button type="submit" class="logout-btn">Log Out</button>
    </form>
</div>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?= htmlspecialchars($username) ?></h1>
        <p style="color:#555;margin-top:4px;">Greenfield Local Hub &mdash; Customer Dashboard</p>
    </div>

    <div class="grid">

        <a href="/PHP_Form_Customer/customer_view_basket_form.php" class="card">
            <h3>Basket</h3>
            <p class="card-number"><?= (int)$basketCount ?></p>
        </a>

        <a href="/PHP_Form_Customer/customer_orders.php" class="card">
            <h3>Order History</h3>
            <p class="card-number"><?= (int)$orderCount ?></p>
        </a>

        <a href="/PHP_Form_Customer/customer_account_settings.php" class="card">
            <h3>Loyalty Points</h3>
            <p class="card-number" style="color:#92400e;"><?= (int)$loyaltyPoints ?></p>
        </a>

        <a href="/PHP_Form_Customer/customer_orders.php" class="card">
            <h3>Active Orders</h3>
            <p class="card-number"><?= (int)$activeOrders ?></p>
        </a>

    </div>
</div>

<script>
const sidebar = document.getElementById("sidebar");
document.getElementById("openSidebar").onclick = () => sidebar.classList.add("active");
document.getElementById("closeSidebar").onclick = () => sidebar.classList.remove("active");
</script>
</body>
</html>

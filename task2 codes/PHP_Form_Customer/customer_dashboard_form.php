<?php
declare(strict_types=1);
session_start();

//  Redirect if not logged in
if (!isset($_SESSION["customer_id"])) {
    header("Location: /PHP_Form/login_choice_form.php");
    exit();
}

//  Load database connection
require_once __DIR__ . "/../PHP_Scripts/db_connection.php";
$conn = get_database_connection();

//  Correct session variable for username
$user_id = $_SESSION["customer_id"];
$username = $_SESSION["customer_username"] ?? "Customer";


// ----------------------
//  DATABASE QUERIES
// ----------------------

//  Basket count
$stmt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) FROM basket WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($basket);
$stmt->fetch();
$stmt->close();

//  Order count
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($orderCount);
$stmt->fetch();
$stmt->close();

//  Loyalty points
$stmt = $conn->prepare("SELECT COALESCE(points_balance,0) FROM loyalty_accounts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($loyalty);
$stmt->fetch();
$stmt->close();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- EXISTING STYLES (UNCHANGED) -->
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">

    <!-- DASHBOARD STYLE -->
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">

    <!-- JS -->
    <script src="/JavaScripts/accessibility.js" defer></script>
    <script src="/JavaScripts/customer_dashboard.js" defer></script>
    <title>Greenfield Hub | Customer Dashboard</title>
</head>


<?php include __DIR__ . "/../components/navigation.php"; ?>

<!-- ✅ OPEN SIDEBAR BUTTON -->
<button id="openSidebar" class="open-btn">☰</button>


<!--  CLEAN STATIC SIDEBAR  -->
<div id="sidebar" class="sidebar active">

    <!-- Close button -->
    <button id="closeSidebar" class="close-btn">×</button>

 <!-- MENU BUTTONS -->
<div class="menu">

    <a  class="menu-btn active">
        Overview
    </a>

    <a href="/customer/orders.php" class="menu-btn">
        My Orders
    </a>

    <a href="/customer/favourites.php" class="menu-btn">
        Favourites/Saved Items
    </a>

    <a href="/customer/account_settings.php" class="menu-btn">
        Account Settings
    </a>

</div>



    <!-- LOGOUT BUTTON -->
  <form action="/PHP_script_auth/customer_logout.php" method="post">
    <button type="submit" class="logout-btn">Log Out</button>
</form>

</div>

<!-- ✅ MAIN DASHBOARD -->
<div class="dashboard-container">
<div class="dashboard-header">
    <h1>Welcome, <?= htmlspecialchars($username) ?></h1>
</div>
    <div class="grid">

        <a href="/PHP_Form_Customer/basket.php" class="card">
            <h3>Basket</h3>
            <p class="card-number"><?= $basket ?></p>
        </a>

        <a href="/PHP_Form_Customer/customer_orders.php" class="card">
            <h3>Order History</h3>
            <p class="card-number"><?= $orderCount ?></p>
        </a>

        <a href="#" class="card">
            <h3>Loyalty Points</h3>
            <p class="card-number"><?= $loyalty ?></p>
        </a>

        <a href="#" class="card">
            <h3>Recommended Products</h3>
        </a>

    </div>

</div>

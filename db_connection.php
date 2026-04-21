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

$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT COALESCE(points_balance,0) FROM loyalty_accounts WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($loyaltyPoints);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <script src="/JavaScripts/accessibility.js" defer></script>
    <style>
        .settings-wrap { max-width:700px; width:100%; padding:0 20px 60px; }
        .settings-card { background:#fff; border:2px solid #e5e7eb; border-radius:14px; padding:28px; margin-bottom:20px; }
        .settings-card h2 { font-size:1.05rem; font-weight:800; color:#134e4a; margin-bottom:18px; }
        .info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee; font-size:0.95rem; }
        .info-row:last-child { border-bottom:none; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-weight:600; margin-bottom:4px; font-size:0.9rem; }
        .form-group input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #aaa; font-size:0.95rem; box-sizing:border-box; }
        .save-btn { padding:10px 24px; border-radius:30px; background:#c6d083; border:2px solid #c6d083; font-weight:700; cursor:pointer; transition:0.2s; }
        .save-btn:hover { background:#b8c870; transform:translateY(-2px); }
        .loyalty-card { background:#fef9c3; border:2px solid #fde68a; }
        .loyalty-points-big { font-size:52px; font-weight:900; color:#92400e; text-align:center; margin:10px 0; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<button id="openSidebar" class="open-btn">&#9776;</button>
<div id="sidebar" class="sidebar active">
    <button id="closeSidebar" class="close-btn">&times;</button>
    <div class="menu">
        <a href="/PHP_Form_Customer/customer_dashboard_form.php" class="menu-btn">Overview</a>
        <a href="/PHP_Form_Customer/customer_orders.php" class="menu-btn">My Orders</a>
        <a href="/PHP_Form_Customer/customer_account_settings.php" class="menu-btn active">Account Settings</a>
    </div>
    <form action="/php_script_auth/customer_logout.php" method="post">
        <button type="submit" class="logout-btn">Log Out</button>
    </form>
</div>

<div class="dashboard-container">
    <div class="dashboard-header"><h1>Account Settings</h1></div>

    <div class="settings-wrap">

        <?php if (!empty($_SESSION['flash_msg'])): ?>
            <div style="padding:12px 16px;border-radius:10px;margin-bottom:16px;background:<?= ($_SESSION['flash_type'] ?? 'ok') === 'err' ? '#fee2e2' : '#dcfce7' ?>;">
                <?= htmlspecialchars($_SESSION['flash_msg']) ?>
            </div>
            <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- Account Info -->
        <div class="settings-card">
            <h2>Account Information</h2>
            <div class="info-row"><span><strong>Name</strong></span><span><?= htmlspecialchars($user['name'] ?? '') ?></span></div>
            <div class="info-row"><span><strong>Email</strong></span><span><?= htmlspecialchars($user['email'] ?? '') ?></span></div>
            <div class="info-row"><span><strong>Member Since</strong></span><span><?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?></span></div>
        </div>

        <!-- Loyalty Points -->
        <div class="settings-card loyalty-card">
            <h2>&#127775; Loyalty Points</h2>
            <div class="loyalty-points-big"><?= (int)$loyaltyPoints ?></div>
            <p style="text-align:center;font-size:0.9rem;color:#555;">
                You earn 1 point per &pound;1 spent. Points can be redeemed at checkout (1 pt = &pound;0.01, up to 10% off).
            </p>
        </div>

        <!-- Change Name -->
        <div class="settings-card">
            <h2>Update Name</h2>
            <form action="/PHP_Scripts_Customer/customer_update_account.php" method="POST">
                <input type="hidden" name="action" value="name">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                <button type="submit" class="save-btn">Save Name</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="settings-card">
            <h2>Change Password</h2>
            <form action="/PHP_Scripts_Customer/customer_update_account.php" method="POST">
                <input type="hidden" name="action" value="password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" minlength="8" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="save-btn">Change Password</button>
            </form>
        </div>

    </div>
</div>

<script>
const sidebar = document.getElementById("sidebar");
document.getElementById("openSidebar").onclick = () => sidebar.classList.add("active");
document.getElementById("closeSidebar").onclick = () => sidebar.classList.remove("active");
</script>
</body>
</html>

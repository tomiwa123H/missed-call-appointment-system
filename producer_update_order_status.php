<?php
declare(strict_types=1);

/* ------------------------------------------------------------
   NAVIGATION SESSION LOGIC
------------------------------------------------------------ */
$isLoggedIn = false;
$displayName = 'Guest';
$dashboardUrl = null;
$logoutUrl = null;

if (isset($_SESSION['role'])) {

    $isLoggedIn = true;

    /* PRODUCER */
    if ($_SESSION['role'] === 'producer') {
        $displayName  = $_SESSION['producer_email'] ?? 'Producer';
        $dashboardUrl = '/PHP_Form_Staff/producer_dashboard_form.php';
        $logoutUrl    = '/php_script_auth/producer_logout.php';
    }

    /* CUSTOMER (future / existing) */
    elseif ($_SESSION['role'] === 'customer') {
        $displayName  = $_SESSION['customer_username'] ?? 'Customer';
        $dashboardUrl = '/PHP_Form_Customer/customer_dashboard_form.php';
        $logoutUrl    = '/php_script_auth/customer_logout.php';
    }
}
?>

<nav class="nav-container">

    <!-- LEFT: LOGO -->
    <div class="nav-left">
        <a href="/index.php">
            <img src="/images/Logo.png"
                 alt="Greenfield Hub Logo"
                 class="nav-logo-img">
        </a>
    </div>

    <!-- CENTER / RIGHT LINKS -->
    <div class="nav-center">

        <a href="/index.php" class="nav-link">Home</a>
        <a href="/PHP_Form/products.php" class="nav-link">Products</a>
        <a href="/contact.html" class="nav-link">Contact</a>

        <span class="divider">|</span>

        <?php if (!$isLoggedIn): ?>

            <a href="/PHP_Form/login_choice_form.php" class="nav-link">Login</a>
            <span class="divider">|</span>
            <a href="/PHP_Form_Customer/customer_register_form.php"
               class="nav-link nav-signup">Sign Up</a>

        <?php else: ?>

            <span class="nav-link" style="font-weight:600;">
                <?= htmlspecialchars($displayName) ?>
            </span>

            <span class="divider">|</span>

            <a href="<?= $dashboardUrl ?>" class="nav-link">
                Dashboard
            </a>

            <span class="divider">|</span>

            <a href="<?= $logoutUrl ?>" class="nav-link">
                Logout
            </a>

        <?php endif; ?>

    </div>
</nav>

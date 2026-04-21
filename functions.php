<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS Paths (correct for your folder structure) -->
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <link rel="stylesheet" href="/style_sheets/customer_auth.css">
<script src="/JavaScripts/customer_dashboard.js" defer></script>
    <title>Customer Login</title>
</head>

<body>

<?php include __DIR__ . '/../components/navigation.php'; ?>

<section class="auth-box">

    <h1 class="auth-title">Customer Login</h1>
    <p class="auth-subtitle">Access your gift shop orders and loyalty points</p>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_msg'])): 
        $type = $_SESSION['flash_type'] ?? 'ok';
    ?>
        <div class="msg <?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($_SESSION['flash_msg']); ?>
        </div>
    <?php 
        unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
    endif; 
    ?>

    <!-- LOGIN FORM -->
<form action="/php_script_auth/customer_login_action.php" method="POST">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required autocomplete="email">

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">

    <button type="submit" class="auth-btn">Log In</button>

</form>

<!-- Register link -->
<a href="/PHP_Form_Customer/customer_register_form.php" class="auth-small-link">
    Don’t have an account? Create one
</a>

<!-- Forgot password -->
<a href="/PHP_Form_Customer/customer_reset_pass_form.php" class="auth-small-link">
    Forgot your password?
</a>

</section>

</body>
</html>
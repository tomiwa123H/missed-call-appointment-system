<?php
declare(strict_types=1);
session_start();

/* Already logged in redirects */
if (isset($_SESSION['customer_id']) && $_SESSION['role'] === 'customer') {
    header("Location: /PHP_Form_Customer/customer_dashboard_form.php");
    exit();
}
if (isset($_SESSION['producer_id']) && $_SESSION['role'] === 'producer') {
    header("Location: /PHP_Form_Staff/producer_dashboard_form.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/login_choice.css">
    <title>Login | Greenfield Hub</title>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<section class="choice-hero">
    <h1>Welcome Back</h1>
    <p>Select how you would like to log in</p>
</section>

<section class="login-choice-section">
    <div class="choice-container">

        <!-- PRODUCER LOGIN -->
        <article class="login-card">
            <h2>Producer Login</h2>
            <p>Access your producer dashboard to manage products and orders.</p>
            <a class="login-btn" href="/PHP_Form_Staff/producer_login_form.php">Producer Login</a>
        </article>

        <!-- CUSTOMER LOGIN -->
        <article class="login-card">
            <h2>Customer Login</h2>
            <p>Log in to shop fresh local produce and earn loyalty points.</p>
            <div class="btn-row">
                <a class="login-btn" href="/PHP_Form_Customer/customer_login_form.php">Login</a>
                <a class="register-btn" href="/PHP_Form_Customer/customer_register_form.php">Register</a>
            </div>
        </article>

    </div>
</section>
</body>
</html>

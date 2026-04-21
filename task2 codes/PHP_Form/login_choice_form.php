<?php
// login_choice.php
session_start();
?>
<?php
// login_choice.php
session_start();

// ✅ If customer is logged in, skip login choice and go to dashboard
if (isset($_SESSION["customer_id"])) {
    header("Location: /PHP_Form_Customer/customer_dashboard_form.php");
    exit;
}

// ✅ If producer/staff is logged in, send them to their dashboard
if (isset($_SESSION["staff_id"])) {
    header("Location: /PHP_Form_Staff/staff_dashboard_form.php");
    exit;
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

<?php include __DIR__ . "/../components/navigation.php"; ?>

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
            <a class="login-btn" href="/PHP_form_Staff/producer_login_form.php">Producer Login</a>
        </article>

        <!-- CUSTOMER LOGIN -->
        <article class="login-card">
            <h2>Customer Login</h2>
            <p>Log in to shop fresh local produce and view your loyalty points.</p>

            <div class="btn-row">
                <a class="login-btn" href="/PHP_form_Customer/customer_login_form.php">Login</a>
                <a class="register-btn" href="/PHP_Form_Customer/customer_register_form.php">Register</a>
            </div>
        </article>

    </div>
</section>

</body>
</html>

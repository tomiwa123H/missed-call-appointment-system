<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <link rel="stylesheet" href="/style_sheets/producer_auth.css">
    <script src="/JavaScripts/accessibility.js" defer></script>
    <title>Norwest Digital | Producer Login</title>
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>

<!-- HERO -->
<section class="hero login-hero">
    <div class="hero-content">
        <h1>Producer Login</h1>
        <p>Access your producer dashboard</p>
    </div>
</section>

<!-- LOGIN FORM -->
<section class="login_container_row">
    <div class="form-box">

        <?php if (!empty($_SESSION['flash_msg'])): ?>
            <p class="error-msg">
                <?= htmlspecialchars($_SESSION['flash_msg']) ?>
            </p>
            <?php unset($_SESSION['flash_msg']); ?>
        <?php endif; ?>

        <!-- FIX: action now points to the correct script path -->
        <form action="/php_script_auth/producer_login_action.php" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autocomplete="email">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit">Log In</button>
        </form>

    </div>
</section>

<!-- INFO -->
<section class="producer-info">
    <h3>Not registered as a producer?</h3>
    <p>
        Producer accounts are created by Green field administrators.
        If you are a producer and would like to list your services on the platform,
        please contact our team.
    </p>
    <a href="/contact.html" class="contact-btn">Contact Us</a>
</section>

</body>
</html>
<?php
declare(strict_types=1);

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style_sheets/root_nav.css">
    <link rel="stylesheet" href="../style_sheets/accessibility.css">
    <link rel="stylesheet" href="../style_sheets/customer_auth.css">

    <title>Create Account</title>
</head>

<body>
<?php include __DIR__ . "/../components/navigation.php"; ?>

<section class="auth-box">

    <h1 class="auth-title">Create Account</h1>
    <p class="auth-subtitle">Register to place gift shop orders and earn loyalty points</p>

    <?php
    if (!empty($_SESSION['flash_msg'])):
        $type = $_SESSION['flash_type'] ?? 'ok';
    ?>
        <div class="msg <?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($_SESSION['flash_msg']) ?>
        </div>
    <?php
        unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
    endif;
    ?>
<form action="/php_script_auth/customer_register.php" method="POST">
    

        <!-- NAME -->
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <!-- EMAIL -->
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <!-- PASSWORD -->
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="auth-btn">Create Account</button>
    </form>

<a href="/PHP_Form_Customer/customer_login_form.php" class="auth-small-link">
    Already registered? Log in
</a>

</section>

</body>
</html>
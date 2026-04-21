<?php
declare(strict_types=1);
session_start();  //  REQUIRED so homepage knows you’re logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Greenfield Local Hub</title>

<link rel="stylesheet" href="/style_sheets/hero.css">
<link rel="stylesheet" href="/style_sheets/home.css">
<link rel="stylesheet" href="/style_sheets/root_nav.css">
<link rel="stylesheet" href="/style_sheets/accessibility.css">
<link rel="stylesheet" href="/style_sheets/cookie.css">
<script src="/JavaScripts/accessibility.js" defer></script>


</head>
<body>
<?php include __DIR__ . "/components/navigation.php"; ?>



<!-- ✅ HERO SLIDESHOW -->
<section class="hero-slideshow">

    <div class="slides">
        <div class="slide" style="background-image:url('/images/tractor_hero.jpg');"></div>
        <div class="slide" style="background-image:url('/images/sheep_hero.jpg');"></div>
        <div class="slide" style="background-image:url('/images/vegetable_hero.jpg');"></div>
        <div class="slide" style="background-image:url('/images/potato_hero.jpg');"></div>
    </div>

    <div class="hero-content">
        <h1>FRESH AND LOCAL FOOD DIRECT<br>FROM OUR FARMERS</h1>

        <a href="/products.php" class="search-products-btn">SEARCH FOR PRODUCTS</a>
    </div>

</section>

<!-- ✅ PROFESSIONAL LOYALTY PROGRAM SECTION -->
<section class="loyalty-banner">

    <div class="loyalty-inner">

        <img src="/images/loyalty_icon.png" alt="Loyalty Icon" class="loyalty-icon">

        <h2>JOIN OUR GREENFIELDS LOYALTY PROGRAM</h2>

        <p class="loyalty-subtext">
            Earn points every time you shop, unlock exclusive member‑only deals, 
            and enjoy special rewards from local farmers across Greenfields.
        </p>

        <ul class="loyalty-benefits">
            <li>✔ Save more with every purchase</li>
            <li>✔ Exclusive offers from local farms</li>
            <li>✔ Early access to seasonal produce</li>
            <li>✔ Member‑only discounts and perks</li>
        </ul>

        <div class="loyalty-btn-row">
            <a href="/PHP_Form_Customer/customer_register_form.php" class="loyalty-btn join">JOIN FREE</a>
            <a href="/PHP_Form/login_choice_form.php" class="loyalty-btn login">LOG IN</a>
        </div>

    </div>

</section>

    

</section>

<script src="JavaScripts/cookie.js"></script>

</body>
</html>
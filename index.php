<?php
declare(strict_types=1);
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

<!-- ✅ Navigation -->
<nav class="nav-container">

    <a href="/index.php" class="nav-logo">GREENFIELD HUB</a>

    <div class="nav-center">
        <a href="/index.php" class="nav-link">HOME</a>
        <a href="/products.php" class="nav-link">PRODUCTS</a>
        <a href="/producers.php" class="nav-link">PRODUCERS</a>
    </div>

    <div class="nav-right">
        <div class="user-menu">
            <img src="/images/user_icon.png" class="user-icon" alt="User Icon">

            <div class="user-dropdown">
                <a href="/signup.php">Sign Up</a>
                <a href="/login.php">Login</a>
            </div>
        </div>
    </div>

</nav>


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
            <a href="/signup.php" class="loyalty-btn join">JOIN FREE</a>
            <a href="/login.php" class="loyalty-btn login">LOG IN</a>
        </div>

    </div>

</section>

    

</section>

<script src="JavaScripts/cookie.js"></script>

</body>
</html>
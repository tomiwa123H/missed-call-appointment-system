<?php
/* Producer Sidebar Component */
?>

<!-- ☰ OPEN SIDEBAR -->
<button id="openSidebar" class="open-btn">☰</button>

<!-- SIDEBAR -->
<div id="sidebar" class="sidebar active">

    <button id="closeSidebar" class="close-btn">×</button>

    <div class="menu">

        <a href="/PHP_Form_Staff/producer_dashboard_form.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) === 'producer_dashboard_form.php' ? 'active' : '' ?>">
            Overview
        </a>

        <a href="/PHP_Form_Staff/producer_products_list_form.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) === 'producer_products_list_form.php' ? 'active' : '' ?>">
            My Products
        </a>

        <a href="/PHP_Form_Staff/producer_add_product_form.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) === 'producer_add_product_form.php' ? 'active' : '' ?>">
            Add Product
        </a>

        <a href="/PHP_Form_Staff/producer_update_products_form.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) === 'producer_update_products_form.php' ? 'active' : '' ?>">
            Stock Levels
        </a>

        <a href="/PHP_Form_Staff/producer_orders_list_form.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) === 'producer_orders_list_form.php' ? 'active' : '' ?>">
            Orders
        </a>
    </div>

    <form action="/php_script_auth/producer_logout.php" method="post">
        <button type="submit" class="logout-btn">Log Out</button>
    </form>

</div>
``
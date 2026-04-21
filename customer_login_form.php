<?php
declare(strict_types=1);
require_once __DIR__ . '/../php_script_auth/producer_auth_check.php';
requireProducerLogin();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>

    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">
    <link rel="stylesheet" href="/style_sheets/producer_dashboard.css">
</head>

<body>

<?php include __DIR__ . '/../components/navigation.php'; ?>
<?php include __DIR__ . '/../components/producer_sidebar.php'; ?>

<div class="dashboard-container">

    <div class="dashboard-header">
        <h1>Add New Product</h1>
    </div>

    <section class="form-section">

        <?php if (!empty($_SESSION['flash_msg'])): ?>
            <div class="flash-message <?= $_SESSION['flash_type'] ?? 'ok' ?>">
                <?= htmlspecialchars($_SESSION['flash_msg']) ?>
            </div>
            <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <form action="/PHP_Scripts_producer/producer_add_product.php"
      method="POST"
      enctype="multipart/form-data"
      class="product-form">

    <!-- BASIC INFO -->
    <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" required>
    </div>

    <div class="form-group full">
        <label>Description</label>
        <textarea name="description" placeholder="Short description of the product"></textarea>
    </div>

    <!-- PRICE & UNIT -->
    <div class="form-row">
        <div class="form-group">
            <label>Price (£)</label>
            <input type="number" step="0.01" name="price" required>
        </div>

        <div class="form-group">
            <label>Unit</label>
            <input type="text" name="unit" placeholder="e.g. each, kg, pack" required>
        </div>
    </div>

    <!-- STOCK -->
    <div class="form-row">
        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" name="stock_qty" min="0" required>
        </div>

        <div class="form-group">
            <label>Low Stock Threshold</label>
            <input type="number" name="low_stock_threshold" min="0" required>
        </div>
    </div>

    <!-- IMAGE -->
    <div class="form-group full">
        <label>Product Image</label>
        <input type="file" name="image" accept="image/*">
    </div>

    <!-- ACTIONS -->
    <div class="form-actions">
        <a href="/PHP_Form_Staff/producer_products_list_form.php" class="action-cancel">
            Cancel
        </a>

        <button type="submit" class="action-submit">
            Add Product
        </button>
    </div>

</form>

    </section>

</div>

<script>
const sidebar = document.getElementById("sidebar");
openSidebar.onclick = () => sidebar.classList.add("active");
closeSidebar.onclick = () => sidebar.classList.remove("active");
</script>

</body>
</html>

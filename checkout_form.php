<?php
declare(strict_types=1);
require_once __DIR__ . '/../php_script_auth/producer_auth_check.php';
requireProducerLogin();

/* ============================================================
   DATABASE CONNECTION
============================================================ */
require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$producer_id = $_SESSION['producer_id'];

/* ============================================================
   FETCH PRODUCER PRODUCTS
============================================================ */
$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.unit,
        p.is_active,
        i.stock_qty,
        i.low_stock_threshold
    FROM products p
    JOIN inventory i ON i.product_id = p.product_id
    WHERE p.producer_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param("i", $producer_id);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products</title>

    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/producer_dashboard.css">
</head>

<body>

<?php include __DIR__ . '/../components/navigation.php'; ?>
<?php include __DIR__ . '/../components/producer_sidebar.php'; ?>

<div class="dashboard-container">
<!-- PRODUCT ACTION MENU -->
<!-- ACTION BAR -->
<div class="product-action-bar">

    <div class="product-action-text">
        <h2>Product Management</h2>
        <p>Add new products, manage existing listings or update stock.</p>
    </div>

    <div class="product-action-buttons">
        <a href="/PHP_Form_Staff/producer_add_product_form.php" class="action-btn primary">
            + Add Product
        </a>

        <a href="/PHP_Form_Staff/producer_products_list_form.php" class="action-btn">
            Edit Products
        </a>

        <a href="/PHP_Form_Staff/producer_dashboard_form.php" class="action-btn secondary">
            Cancel
        </a>
    </div>

</div>

<!-- PAGE TITLE -->
<div class="dashboard-header">
    <h1>My Products</h1>
</div>


    <div class="grid">

        <?php if (empty($products)): ?>
            <p>No products have been added yet.</p>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
            <div class="card">

                <h3><?= htmlspecialchars($product['name']) ?></h3>

                <p>£<?= number_format((float)$product['price'], 2) ?> <?= htmlspecialchars($product['unit']) ?></p>

                <p>
                    Stock: <?= (int)$product['stock_qty'] ?>
                    <?php if ($product['stock_qty'] <= $product['low_stock_threshold']): ?>
                        <strong style="color:red;">(Low)</strong>
                    <?php endif; ?>
                </p>

                <p>Status: <?= $product['is_active'] ? 'Active' : 'Inactive' ?></p>

                <div style="display:flex; gap:0.5rem;">

                    <a href="/PHP_Form_Staff/producer_update_products_form.php?product_id=<?= $product['product_id'] ?>"
                       class="menu-btn">Edit</a>

                    <form action="/PHP_Scripts_producer/producer_delete_product.php" method="post">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <button class="menu-btn" style="background:#d9534f;color:white;">Delete</button>
                    </form>

                </div>

            </div>
        <?php endforeach; ?>

    </div>
</div>

<script>
const sidebar = document.getElementById("sidebar");
openSidebar.onclick = () => sidebar.classList.add("active");
closeSidebar.onclick = () => sidebar.classList.remove("active");
</script>

</body>
</html>




<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

/* ------------------------------------------------------------
   FETCH ACTIVE PRODUCTS
------------------------------------------------------------ */
$sql = "
    SELECT
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.unit,
        p.image,
        i.stock_qty,
        pr.farm_name
    FROM products p
    JOIN inventory i ON i.product_id = p.product_id
    JOIN producers pr ON pr.producer_id = p.producer_id
    WHERE p.is_active = 1
      AND i.stock_qty > 0
      AND pr.approved = 1
    ORDER BY p.name
";

$result   = $conn->query($sql);
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Greenfield Hub | Products</title>

    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/gift_shop.css">

    <style>
    /* GRID LAYOUT */
    .product-list{
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }

    .product-card{
        background: #fff;
        border-radius: 14px;
        border: 2px solid #e5e7eb;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .product-image{
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 12px;
        background: #f3f4f6;
    }

    .product-name{
        font-weight: 800;
        font-size: 1rem;
        color: #134e4a;
    }

    .product-meta{
        font-size: 0.9rem;
        opacity: 0.85;
    }

    .product-description{
        font-size: 0.9rem;
        line-height: 1.4;
        opacity: 0.9;
    }

    .product-actions{
        margin-top: auto;
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn{
        padding: 8px 12px;
        border-radius: 10px;
        border: none;
        font-weight: 700;
        background: #16a34a;
        color: #fff;
        cursor: pointer;
    }

    .qty{
        width: 60px;
        padding: 6px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
    }

    .no-products{
        padding: 30px;
        text-align: center;
        background: #fff;
        border-radius: 14px;
        border: 2px dashed #d1d5db;
    }
    </style>
</head>

<body>

<?php include __DIR__ . '/../components/navigation.php'; ?>

<?php if (!empty($_SESSION['flash_msg'])): ?>
    <div style="max-width:900px;margin:90px auto 0;padding:0 20px;">
        <div style="padding:12px 16px;border-radius:10px;background:<?= ($_SESSION['flash_type'] ?? 'ok') === 'err' ? '#fee2e2' : '#dcfce7' ?>;margin-bottom:12px;">
            <?= htmlspecialchars($_SESSION['flash_msg']) ?>
        </div>
    </div>
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<!-- HERO -->
<section class="hero login-hero">
    <div class="hero-content">
        <h1>Greenfield Hub Products</h1>
        <p>Browse fresh products from our approved producers</p>
    </div>
</section>

<!-- PRODUCTS -->
<section class="products-wrap">

<?php if (empty($products)): ?>
    <div class="no-products">
        <p>No products available at the moment.</p>
    </div>
<?php else: ?>

<div class="product-list">

<?php foreach ($products as $product): ?>
    <div class="product-card">

        <!-- IMAGE -->
        <?php if (!empty($product['image'])): ?>
            <img
                src="/uploads/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>"
                class="product-image">
        <?php else: ?>
            <img
                src="/images/placeholder.png"
                alt="No image"
                class="product-image">
        <?php endif; ?>

        <!-- INFO -->
        <div class="product-name">
            <?= htmlspecialchars($product['name']) ?>
        </div>

        <div class="product-meta">
            <strong>Producer:</strong>
            <?= htmlspecialchars($product['farm_name'] ?? 'Unknown producer') ?>
        </div>

        <div class="product-meta">
            <strong>Price:</strong>
            £<?= number_format((float)$product['price'], 2) ?>
            <?= htmlspecialchars($product['unit']) ?>
        </div>

        <?php if (!empty($product['description'])): ?>
            <div class="product-description">
                <?= htmlspecialchars($product['description']) ?>
            </div>
        <?php endif; ?>

        <!-- ACTIONS -->
        <div class="product-actions">
            <form action="/PHP_Scripts_Customer/customer_add_to_basket.php" method="POST">
                <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                <input type="number"
                       name="quantity"
                       value="1"
                       min="1"
                       max="<?= (int)$product['stock_qty'] ?>"
                       class="qty"
                       required>
                <button type="submit" class="btn">Add</button>
            </form>
        </div>

    </div>
<?php endforeach; ?>

</div>
<?php endif; ?>

</section>

</body>
</html>
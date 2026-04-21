<?php
declare(strict_types=1);
session_start();

/* Auth check */
if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'producer' ||
    !isset($_SESSION['producer_id'])
) {
    header("Location: /PHP_Form/login_choice_form.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$producer_id = (int)$_SESSION['producer_id'];
$productId   = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($productId <= 0) {
    header("Location: /PHP_Form_Staff/producer_products_list_form.php");
    exit();
}

/* Fetch product — must belong to this producer */
$stmt = $conn->prepare("
    SELECT
        p.product_id, p.name, p.description, p.price,
        p.unit, p.image, p.is_active,
        i.stock_qty, i.low_stock_threshold
    FROM products p
    JOIN inventory i ON i.product_id = p.product_id
    WHERE p.product_id = ? AND p.producer_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $productId, $producer_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$product) {
    header("Location: /PHP_Form_Staff/producer_products_list_form.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product | Greenfield Hub</title>
    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/producer_dashboard.css">
    <link rel="stylesheet" href="/style_sheets/customer_dashboard.css">
</head>
<body>
<?php include __DIR__ . '/../components/navigation.php'; ?>
<?php include __DIR__ . '/../components/producer_sidebar.php'; ?>

<div class="dashboard-container">

    <div class="dashboard-header">
        <h1>Edit Product</h1>
    </div>

    <section class="form-section" style="width:100%;max-width:700px;padding:0 20px 40px;">

        <?php if (!empty($_SESSION['flash_msg'])): ?>
            <div class="flash-message <?= $_SESSION['flash_type'] ?? 'ok' ?>" style="margin-bottom:16px;padding:12px 16px;border-radius:10px;background:<?= ($_SESSION['flash_type'] ?? 'ok') === 'err' ? '#fee2e2' : '#dcfce7' ?>;border:1px solid <?= ($_SESSION['flash_type'] ?? 'ok') === 'err' ? '#f87171' : '#86efac' ?>;">
                <?= htmlspecialchars($_SESSION['flash_msg']) ?>
            </div>
            <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <form action="/PHP_Scripts_producer/producer_update_product.php"
              method="POST"
              enctype="multipart/form-data"
              class="product-form">

            <input type="hidden" name="product_id" value="<?= $productId ?>">

            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group full">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price (&pound;)</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars((string)$product['price']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" name="unit" value="<?= htmlspecialchars($product['unit'] ?? '') ?>" placeholder="e.g. each, kg, pack" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_qty" min="0" value="<?= (int)$product['stock_qty'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" min="0" value="<?= (int)$product['low_stock_threshold'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="is_active" style="padding:10px 12px;border-radius:8px;border:1px solid #aaa;font-size:0.95rem;">
                    <option value="1" <?= $product['is_active'] ? 'selected' : '' ?>>Active (visible to customers)</option>
                    <option value="0" <?= !$product['is_active'] ? 'selected' : '' ?>>Inactive (hidden)</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Replace Image (optional)</label>
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($product['image'])): ?>
                    <img src="/uploads/<?= htmlspecialchars($product['image']) ?>"
                         alt="Current image"
                         style="margin-top:10px;max-width:180px;border-radius:10px;">
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a href="/PHP_Form_Staff/producer_products_list_form.php" class="action-cancel">Cancel</a>
                <button type="submit" class="action-submit">Save Changes</button>
            </div>

        </form>

        <!-- Delete form -->
        <form action="/PHP_Scripts_producer/producer_delete_product.php"
              method="POST"
              style="margin-top:16px;text-align:right;"
              onsubmit="return confirm('Permanently delete this product?');">
            <input type="hidden" name="product_id" value="<?= $productId ?>">
            <button type="submit"
                    style="padding:10px 22px;border-radius:30px;background:#fee2e2;border:2px solid #f87171;color:#b91c1c;font-weight:700;cursor:pointer;">
                Delete Product
            </button>
        </form>

    </section>
</div>

<script>
const sidebar = document.getElementById("sidebar");
document.getElementById("openSidebar").onclick = () => sidebar.classList.add("active");
document.getElementById("closeSidebar").onclick = () => sidebar.classList.remove("active");
</script>
</body>
</html>

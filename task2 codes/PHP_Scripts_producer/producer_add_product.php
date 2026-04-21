<?php
declare(strict_types=1);
require_once __DIR__ . '/../php_script_auth/producer_auth_check.php';
requireProducerLogin();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /PHP_Form_Staff/producer_add_product_form.php");
    exit();
}

$name        = trim($_POST['name']        ?? '');
$description = trim($_POST['description'] ?? '');
$price       = (float)($_POST['price']    ?? 0);
$unit        = trim($_POST['unit']        ?? '');
$stockQty    = (int)($_POST['stock_qty']  ?? 0);
$threshold   = (int)($_POST['low_stock_threshold'] ?? 5);
$producer_id = (int)$_SESSION['producer_id'];

if ($name === '' || $price <= 0 || $unit === '') {
    $_SESSION['flash_msg']  = 'Please fill in all required fields.';
    $_SESSION['flash_type'] = 'err';
    header("Location: /PHP_Form_Staff/producer_add_product_form.php");
    exit();
}

/* ---- Image upload ---- */
$imageName = null;
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES['image']['type'], $allowedTypes, true)) {
        $_SESSION['flash_msg']  = 'Only JPG, PNG or WEBP images are allowed.';
        $_SESSION['flash_type'] = 'err';
        header("Location: /PHP_Form_Staff/producer_add_product_form.php");
        exit();
    }
    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        $_SESSION['flash_msg']  = 'Image must be under 2MB.';
        $_SESSION['flash_type'] = 'err';
        header("Location: /PHP_Form_Staff/producer_add_product_form.php");
        exit();
    }
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $safeName  = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['image']['name']));
    $imageName = time() . '_' . $safeName;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
        $_SESSION['flash_msg']  = 'Image upload failed. Please try again.';
        $_SESSION['flash_type'] = 'err';
        header("Location: /PHP_Form_Staff/producer_add_product_form.php");
        exit();
    }
}

/* ---- Insert product ---- */
$stmt = $conn->prepare("
    INSERT INTO products (producer_id, name, description, price, unit, image, is_active)
    VALUES (?, ?, ?, ?, ?, ?, 1)
");
$stmt->bind_param("issdss", $producer_id, $name, $description, $price, $unit, $imageName);
$stmt->execute();
$product_id = $stmt->insert_id;
$stmt->close();

/* ---- Insert inventory ---- */
$stmt = $conn->prepare("
    INSERT INTO inventory (product_id, stock_qty, low_stock_threshold)
    VALUES (?, ?, ?)
");
$stmt->bind_param("iii", $product_id, $stockQty, $threshold);
$stmt->execute();
$stmt->close();

$conn->close();

$_SESSION['flash_msg']  = "Product \"" . htmlspecialchars($name) . "\" added successfully.";
$_SESSION['flash_type'] = 'ok';
header("Location: /PHP_Form_Staff/producer_products_list_form.php");
exit();
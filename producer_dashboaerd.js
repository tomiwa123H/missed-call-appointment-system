<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /PHP_Form/products.php");
    exit();
}

$product_id = (int)($_POST['product_id'] ?? 0);
$quantity   = (int)($_POST['quantity']   ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    $_SESSION['flash_msg']  = 'Invalid product or quantity.';
    $_SESSION['flash_type'] = 'err';
    header("Location: /PHP_Form/products.php");
    exit();
}

$conn = get_database_connection();

/* Check product exists and has stock */
$stmt = $conn->prepare("
    SELECT p.product_id, p.name, i.stock_qty
    FROM products p
    JOIN inventory i ON i.product_id = p.product_id
    WHERE p.product_id = ? AND p.is_active = 1 AND i.stock_qty > 0
    LIMIT 1
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $_SESSION['flash_msg']  = 'Product not available.';
    $_SESSION['flash_type'] = 'err';
    $conn->close();
    header("Location: /PHP_Form/products.php");
    exit();
}

if ($quantity > (int)$product['stock_qty']) {
    $quantity = (int)$product['stock_qty'];
}

/* If customer is logged in — use DB basket */
if (isset($_SESSION['customer_id'])) {
    $user_id = (int)$_SESSION['customer_id'];

    /* Upsert: if already in basket add qty, else insert */
    $stmt = $conn->prepare("
        INSERT INTO basket (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), ?)
    ");
    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $product['stock_qty']);
    $stmt->execute();
    $stmt->close();
} else {
    /* Guest — use session basket */
    if (!isset($_SESSION['basket'])) $_SESSION['basket'] = [];
    $existing = $_SESSION['basket'][$product_id] ?? 0;
    $_SESSION['basket'][$product_id] = min($existing + $quantity, (int)$product['stock_qty']);
}

$conn->close();

$_SESSION['flash_msg']  = htmlspecialchars($product['name']) . ' added to basket.';
$_SESSION['flash_type'] = 'ok';
header("Location: /PHP_Form/products.php");
exit();

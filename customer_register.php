<?php
declare(strict_types=1);
session_start();

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'producer' ||
    !isset($_SESSION['producer_id'])
) {
    header("Location: /PHP_Form/login_choice_form.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /PHP_Form_Staff/producer_products_list_form.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$product_id  = (int)($_POST['product_id'] ?? 0);
$producer_id = (int)$_SESSION['producer_id'];

if ($product_id <= 0) {
    header("Location: /PHP_Form_Staff/producer_products_list_form.php");
    exit();
}

/* Verify ownership before deleting */
$stmt = $conn->prepare("SELECT product_id FROM products WHERE product_id=? AND producer_id=?");
$stmt->bind_param("ii", $product_id, $producer_id);
$stmt->execute();
$owns = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$owns) {
    $_SESSION['flash_msg']  = 'Product not found.';
    $_SESSION['flash_type'] = 'err';
    header("Location: /PHP_Form_Staff/producer_products_list_form.php");
    exit();
}

/* Delete inventory first (FK constraint) then product */
$stmt = $conn->prepare("DELETE FROM inventory WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM products WHERE product_id=? AND producer_id=?");
$stmt->bind_param("ii", $product_id, $producer_id);
$stmt->execute();
$stmt->close();
$conn->close();

$_SESSION['flash_msg']  = 'Product deleted successfully.';
$_SESSION['flash_type'] = 'ok';
header("Location: /PHP_Form_Staff/producer_products_list_form.php");
exit();

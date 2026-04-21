<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /PHP_Form_Customer/customer_view_basket_form.php");
    exit();
}

$conn = get_database_connection();
$isLoggedIn = isset($_SESSION['customer_id']);
$user_id    = $isLoggedIn ? (int)$_SESSION['customer_id'] : 0;

if (isset($_POST['cancel_basket'])) {
    if ($isLoggedIn) {
        $stmt = $conn->prepare("DELETE FROM basket WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $_SESSION['basket'] = [];
    }
    $conn->close();
    header("Location: /PHP_Form/products.php");
    exit();
}

if (isset($_POST['update_basket'])) {
    $qty = $_POST['qty'] ?? [];
    foreach ($qty as $product_id => $quantity) {
        $product_id = (int)$product_id;
        $quantity   = (int)$quantity;
        if ($isLoggedIn) {
            if ($quantity <= 0) {
                $stmt = $conn->prepare("DELETE FROM basket WHERE user_id=? AND product_id=?");
                $stmt->bind_param("ii", $user_id, $product_id);
            } else {
                $stmt = $conn->prepare("UPDATE basket SET quantity=? WHERE user_id=? AND product_id=?");
                $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            }
            $stmt->execute();
            $stmt->close();
        } else {
            if ($quantity <= 0) {
                unset($_SESSION['basket'][$product_id]);
            } else {
                $_SESSION['basket'][$product_id] = $quantity;
            }
        }
    }
}

$conn->close();
header("Location: /PHP_Form_Customer/customer_view_basket_form.php");
exit();

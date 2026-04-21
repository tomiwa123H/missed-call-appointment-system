<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: /PHP_Form_Customer/customer_login_form.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /PHP_Form_Customer/checkout_form.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$user_id        = (int)$_SESSION['customer_id'];
$fulfilment     = trim($_POST['fulfilment_type'] ?? 'collection');
$use_loyalty    = isset($_POST['use_loyalty']);
$loyalty_disc   = $use_loyalty ? (float)($_POST['loyalty_discount'] ?? 0) : 0.0;
$delivery_addr  = trim($_POST['delivery_address'] ?? '');
$base_total     = (float)($_POST['base_total'] ?? 0);

$allowed_fulfilment = ['collection', 'delivery'];
if (!in_array($fulfilment, $allowed_fulfilment, true)) $fulfilment = 'collection';

/* Load basket from DB */
$stmt = $conn->prepare("
    SELECT b.product_id, b.quantity, p.price, i.stock_qty
    FROM basket b
    JOIN products p ON p.product_id = b.product_id
    JOIN inventory i ON i.product_id = p.product_id
    WHERE b.user_id = ? AND p.is_active = 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$basket_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($basket_items)) {
    header("Location: /PHP_Form_Customer/customer_view_basket_form.php");
    exit();
}

/* Validate stock and calculate total */
$order_total = 0.0;
foreach ($basket_items as $item) {
    if ((int)$item['quantity'] > (int)$item['stock_qty']) {
        $_SESSION['flash_msg']  = 'Some items are no longer in stock. Please review your basket.';
        $_SESSION['flash_type'] = 'err';
        $conn->close();
        header("Location: /PHP_Form_Customer/customer_view_basket_form.php");
        exit();
    }
    $order_total += $item['quantity'] * (float)$item['price'];
}

if ($fulfilment === 'delivery') $order_total += 2.99;
$order_total -= $loyalty_disc;
if ($order_total < 0) $order_total = 0.0;

/* Insert order */
$stmt = $conn->prepare("
    INSERT INTO orders (customer, total, status, fulfilment_type)
    VALUES (?, ?, 'placed', ?)
");
$stmt->bind_param("ids", $user_id, $order_total, $fulfilment);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

/* Insert order items + deduct stock */
foreach ($basket_items as $item) {
    $line_total = $item['quantity'] * (float)$item['price'];
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, line_total) VALUES (?,?,?,?)");
    $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $line_total);
    $stmt->execute();
    $stmt->close();

    /* Deduct inventory */
    $stmt = $conn->prepare("UPDATE inventory SET stock_qty = stock_qty - ? WHERE product_id = ?");
    $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
    $stmt->execute();
    $stmt->close();
}

/* Clear basket */
$stmt = $conn->prepare("DELETE FROM basket WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

/* Award loyalty points (1 per £1 spent, rounded down) */
$points_earned = (int)floor($order_total);
if ($points_earned > 0) {
    $stmt = $conn->prepare("
        INSERT INTO loyalty_accounts (user_id, points_balance)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE points_balance = points_balance + VALUES(points_balance)
    ");
    $stmt->bind_param("ii", $user_id, $points_earned);
    $stmt->execute();
    $stmt->close();
}

/* Deduct loyalty points if used */
if ($use_loyalty && $loyalty_disc > 0) {
    $points_used = (int)ceil($loyalty_disc / 0.01);
    $stmt = $conn->prepare("
        UPDATE loyalty_accounts SET points_balance = GREATEST(0, points_balance - ?)
        WHERE user_id=?
    ");
    $stmt->bind_param("ii", $points_used, $user_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

/* Store confirmation data in session */
$_SESSION['order_confirmation'] = [
    'order_id'       => $order_id,
    'total'          => $order_total,
    'fulfilment'     => $fulfilment,
    'points_earned'  => $points_earned,
];

header("Location: /PHP_Form_Customer/order_confirmation.php");
exit();

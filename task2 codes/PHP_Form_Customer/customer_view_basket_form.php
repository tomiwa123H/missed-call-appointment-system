<?php

declare(strict_types=1);

require_once __DIR__ . "/../PHP_Scripts/load_file.php";

$conn = get_database_connection();

/*
----------------------------------------------------------
Get basket from session
----------------------------------------------------------
*/

$basket = $_SESSION["basket"] ?? [];

$products = [];
$totalCost = 0;
$totalItems = 0;

if (!empty($basket)) {

    $ids = implode(",", array_map("intval", array_keys($basket)));

    $sql = "
        SELECT
            product_id,
            product_name,
            category,
            price,
            image,
            stock_quantity
        FROM products
        WHERE product_id IN ($ids)
        AND stock_quantity > 0
    ";

    $result = $conn->query($sql);

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $pid = (int)$row["product_id"];

            $qty = $basket[$pid] ?? 0;

            if ($qty > 0) {

                $row["quantity"] = $qty;

                $row["line_total"] = $qty * (float)$row["price"];

                $totalCost += $row["line_total"];
                $totalItems += $qty;

                $products[] = $row;
            }
        }
    }
}

$conn->close();


/*
----------------------------------------------------------
Loyalty points
Example: 1 point per £1 spent
----------------------------------------------------------
*/

$loyaltyPoints = floor($totalCost);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <link rel="stylesheet" href="/style_sheets/hero.css">
    <link rel="stylesheet" href="/style_sheets/basket.css">
    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <script src="/JavaScripts/accessibility.js" defer></script>

    <title>Wild Zoo | Basket</title>

</head>

<body>

    <?php include __DIR__ . "/../components/navigation.php"; ?>

    <!-- HERO -->

    <section class="hero login-hero">

        <div class="hero-content">

            <h1>Your Basket</h1>
            <p>Review items before checkout</p>

        </div>

    </section>


    <section class="basket-wrap">

        <form method="POST" action="/PHP_Scripts_customer/customer_update_basket.php">

            <div class="product-list">

                <?php if (!empty($products)): ?>

                    <?php foreach ($products as $product): ?>

                        <div class="product-card">

                            <div class="product-info">

                                <div class="product-name">

                                    <?= htmlspecialchars($product["product_name"]) ?>

                                </div>

                                <div class="product-meta">
                                    Category: <?= htmlspecialchars($product["category"]) ?>
                                </div>

                                <div class="product-meta">
                                    Price: £<?= number_format((float)$product["price"], 2) ?>
                                </div>

                                <div class="product-meta">
                                    Total: £<?= number_format((float)$product["line_total"], 2) ?>
                                </div>

                            </div>


                            <div class="product-actions">

                                <?php if (!empty($product["image"])): ?>

                                    <img
                                        src="/uploads/<?= htmlspecialchars($product["image"]) ?>"
                                        class="product-image"
                                        alt="product image">

                                <?php endif; ?>


                                <input
                                    type="number"
                                    name="qty[<?= (int)$product["product_id"] ?>]"
                                    value="<?= (int)$product["quantity"] ?>"
                                    min="0"
                                    max="<?= (int)$product["stock_quantity"] ?>">

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-basket">
                        Your basket is empty.
                    </div>

                <?php endif; ?>

            </div>


            <!-- BASKET SUMMARY -->

            <div class="basket-summary">

                <div class="summary-row">

                    <span>Total Items</span>
                    <span><?= $totalItems ?></span>

                </div>

                <div class="summary-row">

                    <span>Total Cost</span>
                    <span>£<?= number_format($totalCost, 2) ?></span>

                </div>

                <div class="summary-row">

                    <span>Loyalty Points</span>
                    <span><?= $loyaltyPoints ?></span>

                </div>

            </div>




            <!-- ACTION BUTTONS -->

            <div class="action-bar">

                <?php if (!empty($products)): ?>

                    <button
                        type="submit"
                        name="update_basket"
                        class="btn">
                        Update Basket
                    </button>

                <?php endif; ?>

                <a
                    href="/PHP_Scripts_customer/customer_cancel_basket.php"
                    class="btn">
                    Cancel
                </a>

                <a
                    href="/PHP_Form/checkout_form.php"
                    class="btn">
                    Checkout
                </a>

            </div>

        </form>

    </section>
    <script>
        function confirmClearBasket() {

            return confirm("Clear your basket and return to the gift shop?");

        }
    </script>
</body>

</html>
<?php

// Enables strict type checking to prevent type-related errors
declare(strict_types=1);

/*
|------------------------------------------------------------------
| Load helper files
|------------------------------------------------------------------
*/

// Includes general helper functions (sessions, database, flash messages, redirects)
require_once __DIR__ . "/../PHP_Scripts/load_file.php";

// Includes staff authentication check file
require_once __DIR__ . "/../PHP_Scripts_Staff/staff_auth_check.php";

// Ensures only logged-in staff can access this page
requireStaffLogin();

// Creates a connection to the database
$conn = get_database_connection();

/*
|------------------------------------------------------------------
| Get product ID
|------------------------------------------------------------------
*/

// Checks if product_id exists in URL, converts to integer, otherwise defaults to 0
$productId = isset($_GET["product_id"]) ? (int)$_GET["product_id"] : 0;

// If product ID is invalid
if ($productId <= 0) {

    // Set error message and redirect back to products page
    setFlashAndRedirect(
        "Invalid product ID.",
        "err",
        "/PHP_Form_Staff/staff_view_products_form.php"
    );
}


/*
|------------------------------------------------------------------
| Fetch product from database
|------------------------------------------------------------------
*/

// Prepares SQL query to retrieve product details
$stmt = $conn->prepare("
SELECT
product_id,
product_name,
category,
description,
price,
stock_quantity,
low_stock_threshold,
image
FROM products
WHERE product_id = ?
");

// Binds product ID to query (i = integer)
$stmt->bind_param("i", $productId);

// Executes query
$stmt->execute();

// Gets result set
$result = $stmt->get_result();

// Fetches product data as associative array
$product = $result->fetch_assoc();

// Closes statement
$stmt->close();

// Closes database connection
$conn->close();

// If product was not found
if (!$product) {

    // Set error message and redirect
    setFlashAndRedirect(
        "Product not found.",
        "err",
        "/PHP_Form_Staff/staff_view_products_form.php"
    );
}

?>


<!DOCTYPE html>
<!-- Declares HTML5 document -->

<html lang="en">
<!-- Sets language -->

<head>

    <meta charset="UTF-8">
    <!-- Character encoding -->

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Responsive design settings -->

    <link rel="stylesheet" href="/style_sheets/root_nav.css">
    <!-- Navigation styles -->

    <link rel="stylesheet" href="/style_sheets/accessibility.css">
    <!-- Accessibility styles -->

    <link rel="stylesheet" href="/style_sheets/hero.css">
    <!-- Hero section styles -->

    <link rel="stylesheet" href="/style_sheets/update_product.css">
    <!-- Page-specific styles -->

    <script src="/JavaScripts/accessibility.js" defer></script>
    <!-- Accessibility JavaScript -->

    <title>Update Product</title>
    <!-- Page title -->

</head>


<body>

    <?php include __DIR__ . "/../components/navigation.php"; ?>
    <!-- Includes navigation bar -->



    <!-- HERO SECTION -->

    <section class="hero login-hero">
    <!-- Hero banner section -->

        <div class="hero-content">

            <h1>Update Product</h1>
            <!-- Main heading -->

            <h2>Product #<?= htmlspecialchars((string)$productId) ?></h2>
            <!-- Displays product ID safely -->

            <p>Modify the product details below.</p>

        </div>

    </section>



    <!-- MAIN CONTENT -->

    <section class="my-bookings-wrap">
    <!-- Main layout wrapper -->

        <div class="booking-list-panel update-panel">

            <div class="info-box">

                <strong>Note:</strong> Updating product details will immediately affect
                the gift shop catalogue.
                <!-- Informational warning -->

            </div>



            <form
                method="POST"
                action="/PHP_Scripts_Staff/staff_update_products.php"
                enctype="multipart/form-data">
            <!-- Form to update product (POST request + file upload support) -->


                <input
                    type="hidden"
                    name="product_id"
                    value="<?= htmlspecialchars((string)$productId) ?>">
                <!-- Hidden input to send product ID -->



                <div class="update-container">


                    <!-- LEFT COLUMN -->

                    <div class="form-col-left">

                        <h3>1. Product Details</h3>


                        <div>

                            <label>Product Name</label>

                            <input
                                type="text"
                                name="product_name"
                                value="<?= htmlspecialchars($product["product_name"]) ?>"
                                required>
                            <!-- Input pre-filled with product name -->

                        </div>


                        <div>

                            <label>Category</label>

                            <select name="category" required>

                                <option value="Cuddly Toys"
                                    <?= $product["category"] === "Cuddly Toys" ? "selected" : "" ?>>
                                    Cuddly Toys
                                </option>

                                <option value="Stationary"
                                    <?= $product["category"] === "Stationary" ? "selected" : "" ?>>
                                    Stationary
                                </option>

                                <option value="Jigsaws"
                                    <?= $product["category"] === "Jigsaws" ? "selected" : "" ?>>
                                    Jigsaws
                                </option>

                                <option value="Mugs"
                                    <?= $product["category"] === "Mugs" ? "selected" : "" ?>>
                                    Mugs
                                </option>

                            </select>
                            <!-- Dropdown with current category selected -->

                        </div>


                        <div>

                            <label>Description</label>

                            <textarea name="description"><?= htmlspecialchars($product["description"]) ?></textarea>
                            <!-- Pre-filled description -->

                        </div>

                    </div>



                    <!-- RIGHT COLUMN -->

                    <div class="form-col-right">

                        <h3>2. Stock & Pricing</h3>


                        <div>

                            <label>Price (£)</label>

                            <input
                                type="number"
                                step="0.01"
                                name="price"
                                value="<?= htmlspecialchars((string)$product["price"]) ?>"
                                required>
                            <!-- Price input -->

                        </div>


                        <div>

                            <label>Stock Quantity</label>

                            <input
                                type="number"
                                name="stock_quantity"
                                value="<?= htmlspecialchars((string)$product["stock_quantity"]) ?>"
                                required>
                            <!-- Stock input -->

                        </div>


                        <div>

                            <label>Low Stock Threshold</label>

                            <input
                                type="number"
                                name="low_stock_threshold"
                                value="<?= htmlspecialchars((string)$product["low_stock_threshold"]) ?>">
                            <!-- Threshold input -->

                        </div>


                        <div>

                            <label>Replace Image</label>

                            <input
                                type="file"
                                name="image"
                                accept="image/*">
                            <!-- Optional image upload -->

                        </div>



                        <?php if (!empty($product["image"])): ?>
                        <!-- If product has an image -->

                            <img
                                src="/uploads/<?= htmlspecialchars($product["image"]) ?>"
                                class="product-preview"
                                alt="Current product image">
                            <!-- Displays current image -->

                        <?php endif; ?>


                    </div>

                </div>



                <!-- ACTION BAR -->

                <div class="action-bar">


                    <!-- DELETE PRODUCT -->

                    <form
                        action="/PHP_Scripts_Staff/staff_remove_product.php"
                        method="POST"
                        onsubmit="return confirm('Remove this product permanently?');">
                    <!-- Form to delete product with confirmation -->

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= htmlspecialchars((string)$productId) ?>">

                        <button
                            type="submit"
                            class="btn danger">

                            Delete Product

                        </button>

                    </form>



                    <!-- CANCEL BUTTON -->
                    <button
                        type="button"
                        class="btn secondary"
                        onclick="window.location.href='/PHP_Form_Staff/staff_view_products_form.php';">
                        <!-- Button redirects back without saving -->

                        Cancel

                    </button>


                    <!-- SAVE CHANGES -->
                    <button
                        type="submit"
                        name="action"
                        value="update"
                        class="btn">
                        <!-- Submits form to update product -->

                        Save Changes

                    </button>

                </div>



        </div>


        </form>

        </div>

    </section>


</body>

</html>
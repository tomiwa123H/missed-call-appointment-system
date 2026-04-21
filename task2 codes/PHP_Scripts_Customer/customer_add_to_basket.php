<?php
// Enables strict type checking (helps prevent type-related bugs)
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Load core system files
|--------------------------------------------------------------------------
*/

// Includes external PHP file (likely contains helper functions like database connection, redirect, etc.)
require_once __DIR__ . "/../PHP_Scripts/load_file.php";


/*
|--------------------------------------------------------------------------
| Validate request method
|--------------------------------------------------------------------------
*/

// Function to check if the request method is POST
function validateRequest(): void
{
    // Checks if the request method is not POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        // Sets an error message in session (flash message)
        setFlash("Invalid request.", "err");

        // Redirects user back to the form page
        redirect("/PHP_Form/gift_shop_form.php");

    }
}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

// Function to retrieve form data from POST request
function getFormData(): array
{
    return [

        // Gets product_id from form, defaults to 0 if not set, casts to integer
        "product_id" => (int)($_POST["product_id"] ?? 0),

        // Gets quantity from form, defaults to 0 if not set, casts to integer
        "quantity"   => (int)($_POST["quantity"] ?? 0)

    ];
}


/*
|--------------------------------------------------------------------------
| Validate product selection
|--------------------------------------------------------------------------
*/

// Function to validate form input
function validateInput(array $data): void
{

    // Checks if product_id is invalid (less than or equal to 0)
    if ($data["product_id"] <= 0) {

        // Sets error message
        setFlash("Invalid product.", "err");

        // Redirects back to form
        redirect("/PHP_Form/gift_shop_form.php");

    }

    // Checks if quantity is invalid (less than or equal to 0)
    if ($data["quantity"] <= 0) {

        // Sets error message
        setFlash("Quantity must be greater than zero.", "err");

        // Redirects back to form
        redirect("/PHP_Form/gift_shop_form.php");

    }

}


/*
|--------------------------------------------------------------------------
| Fetch product from database
|--------------------------------------------------------------------------
*/

// Function to get product details from database using product ID
function getProduct(mysqli $conn, int $productId): ?array
{

    // SQL query to select product_id and stock_quantity
    $sql = "
        SELECT
        product_id,
        stock_quantity
        FROM products
        WHERE product_id = ?
        LIMIT 1
    ";

    // Prepares SQL statement (prevents SQL injection)
    $stmt = $conn->prepare($sql);

    // Checks if statement preparation failed
    if (!$stmt) {

        // Sets error message
        setFlash("Database error.", "err");

        // Redirects back to form
        redirect("/PHP_Form/gift_shop_form.php");

    }

    // Binds product ID to the query (i = integer)
    $stmt->bind_param("i", $productId);

    // Executes the query
    $stmt->execute();

    // Gets result set
    $result = $stmt->get_result();

    // Fetches result as associative array
    $product = $result->fetch_assoc();

    // Closes the statement
    $stmt->close();

    // Returns product data or null if not found
    return $product ?: null;
}


/*
|--------------------------------------------------------------------------
| Validate stock availability
|--------------------------------------------------------------------------
*/

// Function to check if enough stock is available
function validateStock(array $product, int $quantity): void
{

    // Gets stock quantity and ensures it is an integer
    $stock = (int)$product["stock_quantity"];

    // Checks if product is out of stock
    if ($stock <= 0) {

        // Sets error message
        setFlash("This product is out of stock.", "err");

        // Redirects back to form
        redirect("/PHP_Form/gift_shop_form.php");

    }

    // Checks if requested quantity exceeds available stock
    if ($quantity > $stock) {

        // Sets error message showing available stock
        setFlash("Only $stock items available.", "err");

        // Redirects back to form
        redirect("/PHP_Form/gift_shop_form.php");

    }

}


/*
|--------------------------------------------------------------------------
| Add product to basket session
|--------------------------------------------------------------------------
*/

// Function to add item to session-based basket
function addToBasket(int $productId, int $quantity, int $stock): void
{

    // Checks if basket session exists, if not creates it as an empty array
    if (!isset($_SESSION["basket"])) {

        $_SESSION["basket"] = [];

    }

    /*
    ----------------------------------------------------------
    If product already exists in basket
    ----------------------------------------------------------
    */

    // Checks if product already exists in basket
    if (isset($_SESSION["basket"][$productId])) {

        // Adds new quantity to existing quantity
        $newQty = $_SESSION["basket"][$productId] + $quantity;

        // Ensures quantity does not exceed stock
        if ($newQty > $stock) {

            $newQty = $stock;

        }

        // Updates basket with new quantity
        $_SESSION["basket"][$productId] = $newQty;

    } else {

        // If product not in basket, add it with given quantity
        $_SESSION["basket"][$productId] = $quantity;

    }

}


/*
|--------------------------------------------------------------------------
| Main controller
|--------------------------------------------------------------------------
*/

// Main function to handle adding item to basket
function handleAddToBasket(): void
{

    // Step 1: Validate request type
    validateRequest();

    // Step 2: Get form data
    $data = getFormData();

    // Step 3: Validate input data
    validateInput($data);

    // Step 4: Get database connection
    $conn = get_database_connection();

    // Step 5: Fetch product from database
    $product = getProduct($conn, $data["product_id"]);

    // If product not found
    if (!$product) {

        // Set error message
        setFlash("Product not found.", "err");

        // Redirect back to form
        redirect("/PHP_Form/gift_shop_form.php");

    }

    // Step 6: Validate stock availability
    validateStock($product, $data["quantity"]);

    // Step 7: Add product to basket
    addToBasket(
        $data["product_id"],
        $data["quantity"],
        (int)$product["stock_quantity"]
    );

    // Close database connection
    $conn->close();

    // Set success message
    setFlash("Product added to basket.", "ok");

    // Redirect back to form
    redirect("/PHP_Form/gift_shop_form.php");

}


/*
|--------------------------------------------------------------------------
| Run controller
|--------------------------------------------------------------------------
*/

// Executes the main function when script runs
handleAddToBasket();
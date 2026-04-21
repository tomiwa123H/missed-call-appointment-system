<?php
// Enables strict type checking to reduce type-related errors
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Load core system files
|--------------------------------------------------------------------------
*/

// Includes helper file (sessions, database connection, flash messages, redirects)
require_once __DIR__ . "/../PHP_Scripts/load_file.php";

// Includes staff authentication check file
require_once __DIR__ . "/staff_auth_check.php";

// Ensures only logged-in staff can access this script
requireStaffLogin();



/*
|--------------------------------------------------------------------------
| Validate request
|--------------------------------------------------------------------------
*/

// Function to ensure the request method is POST
function validateRequest(): void
{
    // If request method is not POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        // Set error message
        setFlash("Invalid request method.", "err");

        // Redirect back to product list page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }
}



/*
|--------------------------------------------------------------------------
| Get product ID
|--------------------------------------------------------------------------
*/

// Function to retrieve product ID from form data
function getProductId(): int
{
    // Get product_id from POST, default to 0 if not set, cast to integer
    return (int)($_POST["product_id"] ?? 0);
}



/*
|--------------------------------------------------------------------------
| Check product exists
|--------------------------------------------------------------------------
*/

// Function to fetch product from database
function getProduct(mysqli $conn, int $productId): ?array
{
    // SQL query to get product ID and name
    $sql = "SELECT product_id, product_name FROM products WHERE product_id = ?";

    // Prepare SQL statement
    $stmt = $conn->prepare($sql);

    // If statement preparation fails
    if (!$stmt) {

        // Set error message
        setFlash("Database error.", "err");

        // Redirect back to product list page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Bind product ID to query (i = integer)
    $stmt->bind_param("i", $productId);

    // Execute query
    $stmt->execute();

    // Get result set
    $result = $stmt->get_result();

    // Fetch product as associative array
    $product = $result->fetch_assoc();

    // Close statement
    $stmt->close();

    // Return product data or null if not found
    return $product ?: null;
}



/*
|--------------------------------------------------------------------------
| Delete product
|--------------------------------------------------------------------------
*/

// Function to delete product from database
function deleteProduct(mysqli $conn, int $productId): void
{
    // SQL query to delete product
    $sql = "DELETE FROM products WHERE product_id = ?";

    // Prepare SQL statement
    $stmt = $conn->prepare($sql);

    // If preparation fails
    if (!$stmt) {

        // Set error message
        setFlash("Database error.", "err");

        // Redirect back to product list page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Bind product ID to query
    $stmt->bind_param("i", $productId);

    // Execute query and check for failure
    if (!$stmt->execute()) {

        // Set error message if deletion fails
        setFlash("Unable to delete product.", "err");

        // Redirect back to product list page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Close statement
    $stmt->close();
}



/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
*/

// Main function to handle product removal
function handleRemoveProduct(): void
{
    // Step 1: Validate request method
    validateRequest();

    // Step 2: Get product ID from form
    $productId = getProductId();

    // Check if product ID is valid
    if ($productId <= 0) {

        // Set error message
        setFlash("Invalid product ID.", "err");

        // Redirect back to product list page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Step 3: Connect to database
    $conn = get_database_connection();

    // Step 4: Check if product exists
    $product = getProduct($conn, $productId);

    // If product does not exist
    if (!$product) {

        // Set error message
        setFlash("Product not found.", "err");

        // Redirect back to product list page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Step 5: Delete product from database
    deleteProduct($conn, $productId);

    // Close database connection
    $conn->close();

    // Set success message including product name
    setFlash(
        "Product '{$product["product_name"]}' removed successfully.",
        "ok"
    );

    // Redirect back to product list page
    redirect("/PHP_Form_Staff/staff_view_products_form.php");

}



/*
|--------------------------------------------------------------------------
| Run controller
|--------------------------------------------------------------------------
*/

// Execute the main controller function
handleRemoveProduct();
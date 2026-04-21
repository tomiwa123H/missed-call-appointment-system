<?php
// Enables strict type checking to prevent type-related errors
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Load system files
|--------------------------------------------------------------------------
*/

// Includes helper file (likely contains session, database, redirect and flash functions)
require_once __DIR__ . "/../PHP_Scripts/load_file.php";

// Includes file to check staff authentication
require_once __DIR__ . "/staff_auth_check.php";

// Ensures only logged-in staff can access this page
requireStaffLogin();



/*
|--------------------------------------------------------------------------
| Validate request method
|--------------------------------------------------------------------------
*/

// Function to ensure request is POST
function validateRequest(): void
{
    // If request is not POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        // Set error message
        setFlash("Invalid request method.", "err");

        // Redirect back to product view page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }
}



/*
|--------------------------------------------------------------------------
| Get filter type from form
|--------------------------------------------------------------------------
*/

// Function to get filter type from form input
function getFilter(): string
{
    // Gets filter value from POST, defaults to "all", trims whitespace
    return trim($_POST["filter"] ?? "all");
}



/*
|--------------------------------------------------------------------------
| Get category value
|--------------------------------------------------------------------------
*/

// Function to get category filter value
function getCategory(): ?string
{
    // Gets category, trims it, returns null if empty
    return trim($_POST["category"] ?? "") ?: null;
}



/*
|--------------------------------------------------------------------------
| Fetch products from database
|--------------------------------------------------------------------------
*/

// Function to retrieve products based on filter and category
function fetchProducts(mysqli $conn, string $filter, ?string $category): array
{

    /*
    |--------------------------------------------------------------------------
    | Build SQL dynamically but safely
    |--------------------------------------------------------------------------
    */

    // If filter is for low stock products
    if ($filter === "low_stock") {

        // SQL query to select products where stock is low
        $sql = "
        SELECT *
        FROM products
        WHERE stock_quantity <= low_stock_threshold
        ORDER BY product_name
        ";

        // Prepare SQL statement
        $stmt = $conn->prepare($sql);

    }

    // If filter is by category and category is provided
    elseif ($filter === "category" && $category !== null) {

        // SQL query to select products by category
        $sql = "
        SELECT *
        FROM products
        WHERE category = ?
        ORDER BY product_name
        ";

        // Prepare SQL statement
        $stmt = $conn->prepare($sql);

        // Bind category parameter to query (s = string)
        $stmt->bind_param("s", $category);

    }

    // Default: return all products
    else {

        // SQL query to select all products
        $sql = "
        SELECT *
        FROM products
        ORDER BY product_name
        ";

        // Prepare SQL statement
        $stmt = $conn->prepare($sql);

    }


    // Check if statement preparation failed
    if (!$stmt) {

        // Set error message
        setFlash("Database error preparing query.", "err");

        // Redirect back to form
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }


    // Execute the prepared statement
    $stmt->execute();

    // Get result set from query
    $result = $stmt->get_result();

    // Create empty array to store products
    $products = [];

    // Loop through each row in result set
    while ($row = $result->fetch_assoc()) {

        // Add each product row to products array
        $products[] = $row;

    }

    // Close the statement
    $stmt->close();

    // Return products array
    return $products;

}



/*
|--------------------------------------------------------------------------
| Store results
|--------------------------------------------------------------------------
*/

// Function to store product results in session
function storeResults(array $products): void
{
    // Save products array into session for later use (e.g. display)
    $_SESSION["product_results"] = $products;
}



/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
*/

// Main controller function for viewing products
function handleProductView(): void
{

    // Step 1: Validate request method
    validateRequest();

    // Step 2: Get filter value from form
    $filter = getFilter();

    // Step 3: Get category value (if any)
    $category = getCategory();

    // Step 4: Connect to database
    $conn = get_database_connection();

    // Step 5: Fetch products based on filter
    $products = fetchProducts($conn, $filter, $category);

    // Close database connection
    $conn->close();

    // Step 6: Store results in session
    storeResults($products);

    // Step 7: Redirect back to product view page
    redirect("/PHP_Form_Staff/staff_view_products_form.php");

}



/*
|--------------------------------------------------------------------------
| Run controller
|--------------------------------------------------------------------------
*/

// Execute the controller function
handleProductView();
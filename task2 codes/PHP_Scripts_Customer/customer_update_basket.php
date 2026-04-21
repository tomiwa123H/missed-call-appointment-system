<?php
// Enables strict type checking (helps prevent type-related errors)
declare(strict_types=1);

// Starts the session so basket data can be stored and accessed
session_start();

/*
--------------------------------------------
Update basket quantities
--------------------------------------------
*/

// Function to update quantities of items in the basket
function update_basket_quantities(array $postedQty): void
{
    // If basket does not exist, create an empty basket array
    if (!isset($_SESSION["basket"])) {
        $_SESSION["basket"] = [];
    }

    // Loop through each product ID and quantity submitted from the form
    foreach ($postedQty as $productId => $qty) {

        // Convert product ID to integer for safety
        $productId = (int)$productId;

        // Convert quantity to integer
        $qty = (int)$qty;

        // If quantity is zero or less, remove item from basket
        if ($qty <= 0) {

            remove_item_from_basket($productId);

        } else {

            // Otherwise, update basket with new quantity
            $_SESSION["basket"][$productId] = $qty;

        }
    }
}

/*
--------------------------------------------
Clear basket
--------------------------------------------
*/

// Function to remove all items from the basket
function clear_basket(): void
{
    // Resets basket to an empty array
    $_SESSION["basket"] = [];
}

/*
--------------------------------------------
Remove item from basket
--------------------------------------------
*/

// Function to remove a single product from the basket
function remove_item_from_basket(int $productId): void
{
    // Check if the product exists in the basket
    if (isset($_SESSION["basket"][$productId])) {

        // Remove the product from the basket
        unset($_SESSION["basket"][$productId]);

    }
}


/*
--------------------------------------------
Redirect back to basket page
--------------------------------------------
*/

// Function to redirect the user to the basket page
function redirect_to_basket(): void
{
    // Sends HTTP header to redirect browser to basket page
    header("Location: /PHP_Form_Customer/customer_view_basket_form.php");

    // Stops script execution after redirect
    exit();
}


/*
--------------------------------------------
Main execution
--------------------------------------------
*/

// Checks if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // If cancel button was pressed
    if (isset($_POST["cancel_basket"])) {

        // Clear the entire basket
        clear_basket();

        // Redirect back to basket page
        redirect_to_basket();

        // Stop further execution
        exit();

    }

    // If update basket button was pressed
    if (isset($_POST["update_basket"]))  {

        // Get quantities from POST data, default to empty array if not set
        $qty = $_POST["qty"] ?? [];

        // Update basket quantities using submitted data
        update_basket_quantities($qty);

        // Redirect back to basket page
        redirect_to_basket();

        // Stop further execution
        exit();

    }
}

?>
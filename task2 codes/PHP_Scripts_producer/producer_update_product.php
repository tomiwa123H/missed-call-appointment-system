<?php
// Enables strict type checking to prevent type-related errors
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Load core system files
|--------------------------------------------------------------------------
*/

// Includes helper functions (sessions, database, redirects, flash messages)
require_once __DIR__ . "/../PHP_Scripts/load_file.php";

// Includes staff authentication check file
require_once __DIR__ . "/staff_auth_check.php";

// Ensures only logged-in staff can access this script
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

        // Redirect back to products page
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }
}



/*
|--------------------------------------------------------------------------
| Collect form data
|--------------------------------------------------------------------------
*/

// Function to collect and sanitise form data
function getFormData(): array
{
    return [

        // Get product ID from POST, default 0, convert to integer
        "product_id" => (int)($_POST["product_id"] ?? 0),

        // Get product name and trim whitespace
        "product_name" => trim($_POST["product_name"] ?? ""),

        // Get category and trim whitespace
        "category" => trim($_POST["category"] ?? ""),

        // Get description and trim whitespace
        "description" => trim($_POST["description"] ?? ""),

        // Get price and convert to float
        "price" => (float)($_POST["price"] ?? 0),

        // Get stock quantity and convert to integer
        "stock_quantity" => (int)($_POST["stock_quantity"] ?? 0),

        // Get low stock threshold, default 5
        "low_stock_threshold" => (int)($_POST["low_stock_threshold"] ?? 5)

    ];
}



/*
|--------------------------------------------------------------------------
| Validate product data
|--------------------------------------------------------------------------
*/

// Function to validate input data
function validateProduct(array $data): void
{

    // Check if product ID is valid
    if ($data["product_id"] <= 0) {

        setFlash("Invalid product ID.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Check if product name is empty
    if ($data["product_name"] === "") {

        setFlash("Product name is required.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Check if price is valid
    if ($data["price"] <= 0) {

        setFlash("Price must be greater than zero.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Check if stock quantity is negative
    if ($data["stock_quantity"] < 0) {

        setFlash("Stock quantity cannot be negative.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

}



/*
|--------------------------------------------------------------------------
| Handle image upload (optional)
|--------------------------------------------------------------------------
*/

// Function to handle optional image upload
function handleImageUpload(): ?string
{

    // If no file uploaded, return null (no change to image)
    if (!isset($_FILES["image"]) || $_FILES["image"]["error"] === UPLOAD_ERR_NO_FILE) {

        return null;

    }

    // Check for upload errors
    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

        setFlash("Image upload failed.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Check file size (max 2MB)
    if ($_FILES["image"]["size"] > 2 * 1024 * 1024) {

        setFlash("Image must be under 2MB.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Allowed file types
    $allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    // Get uploaded file type
    $fileType = $_FILES["image"]["type"];

    // Validate file type
    if (!in_array($fileType, $allowedTypes, true)) {

        setFlash("Only JPG, PNG or WEBP images allowed.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Define upload directory
    $uploadDir = __DIR__ . "/../uploads/";

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {

        mkdir($uploadDir, 0755, true);

    }

    // Create safe file name (remove unsafe characters)
    $safeName = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        basename($_FILES["image"]["name"])
    );

    // Add timestamp to filename to avoid duplicates
    $fileName = time() . "_" . $safeName;

    // Full path to save file
    $targetPath = $uploadDir . $fileName;

    // Move uploaded file to target location
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {

        setFlash("Unable to save uploaded image.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Return saved filename
    return $fileName;

}



/*
|--------------------------------------------------------------------------
| Update product in database
|--------------------------------------------------------------------------
*/

// Function to update product details in database
function updateProduct(mysqli $conn, array $data, ?string $newImage): void
{

    // If a new image was uploaded
    if ($newImage !== null) {

        // SQL query including image update
        $sql = "
        UPDATE products
        SET
        product_name = ?,
        category = ?,
        description = ?,
        price = ?,
        stock_quantity = ?,
        low_stock_threshold = ?,
        image = ?
        WHERE product_id = ?
        ";

        // Prepare statement
        $stmt = $conn->prepare($sql);

        // Bind parameters including image
        $stmt->bind_param(
            "sssdissi",
            $data["product_name"],
            $data["category"],
            $data["description"],
            $data["price"],
            $data["stock_quantity"],
            $data["low_stock_threshold"],
            $newImage,
            $data["product_id"]
        );

    }

    // If no new image uploaded
    else {

        // SQL query without image update
        $sql = "
        UPDATE products
        SET
        product_name = ?,
        category = ?,
        description = ?,
        price = ?,
        stock_quantity = ?,
        low_stock_threshold = ?
        WHERE product_id = ?
        ";

        // Prepare statement
        $stmt = $conn->prepare($sql);

        // Bind parameters (no image)
        $stmt->bind_param(
            "sssdiii",
            $data["product_name"],
            $data["category"],
            $data["description"],
            $data["price"],
            $data["stock_quantity"],
            $data["low_stock_threshold"],
            $data["product_id"]
        );

    }

    // Check if statement failed or execution failed
    if (!$stmt || !$stmt->execute()) {

        setFlash("Unable to update product.", "err");
        redirect("/PHP_Form_Staff/staff_view_products_form.php");

    }

    // Close statement
    $stmt->close();

}



/*
|--------------------------------------------------------------------------
| Main controller
|--------------------------------------------------------------------------
*/

// Main function to handle product update
function handleUpdateProduct(): void
{

    // Step 1: Validate request method
    validateRequest();

    // Step 2: Get form data
    $data = getFormData();

    // Step 3: Validate input
    validateProduct($data);

    // Step 4: Handle optional image upload
    $newImage = handleImageUpload();

    // Step 5: Connect to database
    $conn = get_database_connection();

    // Step 6: Update product in database
    updateProduct($conn, $data, $newImage);

    // Close database connection
    $conn->close();

    // Set success message
    setFlash(
        "Product '{$data["product_name"]}' updated successfully.",
        "ok"
    );

    // Redirect back to products page
    redirect("/PHP_Form_Staff/staff_view_products_form.php");

}



/*
|--------------------------------------------------------------------------
| Run controller
|--------------------------------------------------------------------------
*/

// Execute main controller function
handleUpdateProduct();
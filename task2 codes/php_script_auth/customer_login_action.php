<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';

/*
|--------------------------------------------------------------------------
| Validate login input
|--------------------------------------------------------------------------
*/
function validateCustomerInput(string $email, string $password): void {
    if (empty($email) || empty($password)) {
        $_SESSION['flash_msg'] = "Please enter both email and password.";
        $_SESSION['flash_type'] = "err";
        header("Location: /PHP_Form_Customer/customer_login_form.php");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Fetch customer by email
|--------------------------------------------------------------------------
*/
function getCustomerByEmail(mysqli $conn, string $email): ?array {
    $stmt = $conn->prepare("
        SELECT user_id, name, email, password_hash, role 
        FROM users 
        WHERE email = ? 
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc() ?: null;
}

/*
|--------------------------------------------------------------------------
| Authenticate customer
|--------------------------------------------------------------------------
*/
function authenticateCustomer(mysqli $conn, string $email, string $password): void {

    $customer = getCustomerByEmail($conn, $email);

    // ❌ If no match OR password incorrect
    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        $_SESSION['flash_msg'] = "Invalid email or password.";
        $_SESSION['flash_type'] = "err";
        header("Location: /PHP_Form_Customer/customer_login_form.php");
        exit;
    }

    //  Create session
    $_SESSION['customer_id'] = $customer['user_id'];
    $_SESSION['customer_username'] = $customer['name'];
    $_SESSION['role'] = "customer";

    // Redirect to customer dashboard
    header("Location: /PHP_Form_Customer/customer_dashboard_form.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Handle POST login request
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /PHP_Form_Customer/customer_login_form.php");
    exit;
}

$conn = get_database_connection();

$email = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");

validateCustomerInput($email, $password);
authenticateCustomer($conn, $email, $password);


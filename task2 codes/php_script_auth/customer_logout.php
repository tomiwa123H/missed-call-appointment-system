<?php
declare(strict_types=1);

session_start();

// ✅ Must include BOTH:
require_once __DIR__ . '/../PHP_script_auth/customer_auth_check.php'; // login protection
require_once __DIR__ . '/../PHP_Scripts/functions.php';              // flash + redirect helpers

/*
|--------------------------------------------------------------------------
| Remove customer session data
|--------------------------------------------------------------------------
*/
function logoutCustomerUser(): void
{
    unset($_SESSION["customer_id"]);
    unset($_SESSION["customer_username"]);
    unset($_SESSION["loyalty_points"]);
}

/*
|--------------------------------------------------------------------------
| Destroy full session safely
|--------------------------------------------------------------------------
*/
function destroySessionSafely(): void
{
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/*
|--------------------------------------------------------------------------
| Redirect after logout
|--------------------------------------------------------------------------
*/
function redirectAfterLogout(): void
{
    setFlash("You have been logged out successfully.");
    redirect("/index.php");
}

/*
|--------------------------------------------------------------------------
| Main logout controller
|--------------------------------------------------------------------------
*/
function handleCustomerLogout(): void
{
    if ($_SERVER["REQUEST_METHOD"] !== "GET" && $_SERVER["REQUEST_METHOD"] !== "POST") {
        setFlashAndRedirect(
            "Invalid logout request.",
            "err",
            "/index.php"
        );
    }

    logoutCustomerUser();
    destroySessionSafely();
    redirectAfterLogout();
}

handleCustomerLogout();
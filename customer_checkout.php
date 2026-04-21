<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Ensure session is started
|--------------------------------------------------------------------------
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Redirect to a given URL
|--------------------------------------------------------------------------
*/
function redirect(string $path): void
{
    header("Location: " . $path);
    exit;
}

/*
|--------------------------------------------------------------------------
| Set a flash message (stored for ONE request)
|--------------------------------------------------------------------------
*/
function setFlash(string $message, string $type = "ok"): void
{
    $_SESSION['flash_msg']  = $message;
    $_SESSION['flash_type'] = $type;
}

/*
|--------------------------------------------------------------------------
| Set flash message AND redirect in one call
|--------------------------------------------------------------------------
*/
function setFlashAndRedirect(string $message, string $type, string $path): void
{
    setFlash($message, $type);
    redirect($path);
}

/*
|--------------------------------------------------------------------------
| Debug helper (optional)
|--------------------------------------------------------------------------
*/
function dd($item): void
{
    echo "<pre>";
    print_r($item);
    echo "</pre>";
    exit;
}
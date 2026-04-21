
<?php
/*
|--------------------------------------------------------------------------
| FLASH MESSAGE + REDIRECT HELPERS
|--------------------------------------------------------------------------
*/

/**
 * Stores a message in the session to show on the next page load.
 * This is called a "flash message" because it appears once.
 */
function setFlashAndRedirect(string $message, string $type, string $location): void
{
    // Store the message text
    $_SESSION['flash_msg'] = $message;

    // Store the message type (e.g. ok or err)
    $_SESSION['flash_type'] = $type;

    // Redirect the user
    redirect($location);
}

/**
 * Redirects the browser and stops the script.
 */
function redirect(string $location): void
{
    header("Location: $location");
    exit();
}


function setFlash(string $message, string $type = "ok"): void
{
    $_SESSION['flash_msg']  = $message;
    $_SESSION['flash_type'] = $type;
}
?>
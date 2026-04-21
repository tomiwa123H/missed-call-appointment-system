<?php
declare(strict_types=1);
/*
 * producer_auth_check.php
 * -----------------------
 * Include at the very top of every producer-only page BEFORE session_start().
 * It starts the session itself, so calling pages must NOT call session_start() separately.
 *
 * Usage:
 *   require_once __DIR__ . '/../PHP_Scripts/producer_auth_check.php';
 *   requireProducerLogin();
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireProducerLogin(): void
{
    if (
        !isset($_SESSION['role'])        ||
        $_SESSION['role'] !== 'producer' ||
        !isset($_SESSION['producer_id'])
    ) {
        header("Location: /PHP_Form_Staff/producer_login_form.php");
        exit();
    }
}
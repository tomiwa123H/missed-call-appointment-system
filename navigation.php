<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: /PHP_Form_Customer/customer_login_form.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /PHP_Form_Customer/customer_account_settings.php");
    exit();
}

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

$user_id = (int)$_SESSION['customer_id'];
$action  = trim($_POST['action'] ?? '');

if ($action === 'name') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $_SESSION['flash_msg']  = 'Name cannot be empty.';
        $_SESSION['flash_type'] = 'err';
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE user_id=?");
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['customer_username'] = $name;
        $_SESSION['flash_msg']  = 'Name updated successfully.';
        $_SESSION['flash_type'] = 'ok';
    }

} elseif ($action === 'password') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']       ?? '';
    $confirm  = $_POST['confirm_password']   ?? '';

    /* Fetch current hash */
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hash)) {
        $_SESSION['flash_msg']  = 'Current password is incorrect.';
        $_SESSION['flash_type'] = 'err';
    } elseif (strlen($new) < 8) {
        $_SESSION['flash_msg']  = 'New password must be at least 8 characters.';
        $_SESSION['flash_type'] = 'err';
    } elseif ($new !== $confirm) {
        $_SESSION['flash_msg']  = 'Passwords do not match.';
        $_SESSION['flash_type'] = 'err';
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
        $stmt->bind_param("si", $new_hash, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash_msg']  = 'Password changed successfully.';
        $_SESSION['flash_type'] = 'ok';
    }
}

$conn->close();
header("Location: /PHP_Form_Customer/customer_account_settings.php");
exit();

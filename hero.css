<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';
$conn = get_database_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /PHP_Form_Staff/producer_login_form.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['flash_msg'] = 'Please enter both email and password.';
    header("Location: /PHP_Form_Staff/producer_login_form.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT
        u.user_id,
        u.email,
        u.password_hash,
        p.producer_id,
        p.approved
    FROM users u
    JOIN producers p ON p.user_id = u.user_id
    WHERE u.email = ?
      AND u.role = 'producer'
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash_msg'] = 'Invalid email or password.';
    header("Location: /PHP_Form_Staff/producer_login_form.php");
    exit();
}

if ((int)$user['approved'] !== 1) {
    $_SESSION['flash_msg'] = 'Your producer account is awaiting approval.';
    header("Location: /PHP_Form_Staff/producer_login_form.php");
    exit();
}

/* ✅ Store full session including email for display name */
session_regenerate_id(true); // security: prevent session fixation
$_SESSION['role']           = 'producer';
$_SESSION['user_id']        = $user['user_id'];
$_SESSION['producer_id']    = $user['producer_id'];
$_SESSION['producer_email'] = $user['email'];

header("Location: /PHP_Form_Staff/producer_dashboard_form.php");
exit();
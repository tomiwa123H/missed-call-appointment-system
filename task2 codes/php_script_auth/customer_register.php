<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../PHP_Scripts/db_connection.php';

$conn = get_database_connection();

$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = trim($_POST['password']);
$role     = "customer";

/* VALIDATION*/

//  Name must contain ONLY letters + spaces
if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
    $_SESSION['flash_msg'] = "Name can only contain letters and spaces.";
    $_SESSION['flash_type'] = "error";
    header("Location: /PHP_Form_Customer/customer_register_form.php");
    exit;
}

//  Email format check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_msg'] = "Please enter a valid email address.";
    $_SESSION['flash_type'] = "error";
    header("Location: /PHP_Form_Customer/customer_register_form.php");
    exit;
}

//  Password strength check (min 8 chars)
if (strlen($password) < 8) {
    $_SESSION['flash_msg'] = "Password must be at least 8 characters long.";
    $_SESSION['flash_type'] = "error";
    header("Location: /PHP_Form_Customer/customer_register_form.php");
    exit;
}

//  Prevent duplicate accounts
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['flash_msg'] = "An account with this email already exists. Please log in.";
    $_SESSION['flash_type'] = "error";
    header("Location: /PHP_Form_Customer/customer_login_form.php");
    exit;
}
$stmt->close();

//  Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

//  Insert into database
$stmt = $conn->prepare("
    INSERT INTO users (name, email, password_hash, role, created_at, updated_at)
    VALUES (?, ?, ?, ?, NOW(), NOW())
");
$stmt->bind_param("ssss", $name, $email, $password_hash, $role);

if ($stmt->execute()) {

    //  Auto-login user
    $new_user_id = $stmt->insert_id;

    $_SESSION['customer_id'] = $new_user_id;
    $_SESSION['customer_username'] = $name;
    $_SESSION['role'] = "customer";
    $_SESSION['loyalty_points'] = 0;

    //  Redirect to dashboard
    header("Location: /PHP_Form_Customer/customer_dashboard_form.php");
    exit;

} else {
    $_SESSION['flash_msg'] = "Error creating account. Please try again.";
    $_SESSION['flash_type'] = "error";
    header("Location: /PHP_Form_Customer/customer_register_form.php");
    exit;
}
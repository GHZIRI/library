<?php
session_start();
require_once 'db.php';


if($_SERVER["REQUEST_METHOD"] !== 'POST'){
    header("Location: ../views/register.php");
    exit();
}

function validateRegister($full_name, $email, $password, $confirm_password) {
    
    $errors = [];
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
 if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid";
    }
      if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
     if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    return $errors;
}
function emailExists($pdo, $email) {

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    return $stmt->rowCount() > 0;
}
function registerUser($pdo, $full_name, $email, $password) {

    // تشفير كلمة السر قبل الحفظ
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password, role)
        VALUES (?, ?, ?, 'user')
    ");

    $stmt->execute([$full_name, $email, $hashed_password]);
}
$full_name        = trim($_POST['full_name']        ?? '');
$email            = trim($_POST['email']            ?? '');
$password         = trim($_POST['password']        ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

$errors = validateRegister($full_name, $email, $password, $confirm_password);
if (empty($errors) && emailExists($pdo, $email)) {
    $errors[] = "This email is already registered";
}

if (empty($errors)) {
    registerUser($pdo, $full_name, $email, $password);
    header("Location: ../views/login.php?success=1");
    exit();
}
$_SESSION['errors']   = $errors;
$_SESSION['old_name'] = $full_name;
$_SESSION['old_email']= $email;
header("Location: ../views/register.php");
exit();

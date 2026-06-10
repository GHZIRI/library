<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// ============================================
// Helper Functions (used by register.php, etc.)
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($input) {
    return trim(strip_tags($input));
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash($type) {
    $message = $_SESSION['flash_' . $type] ?? null;
    unset($_SESSION['flash_' . $type]);
    return $message;
}

function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function createUser($data) {
    global $pdo;
    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')");
    return $stmt->execute([$data['name'], $data['email'], $hashed]);
}

// ============================================
// Action Handler
// Only runs when functions.php is the direct target (POST form submission)
// ============================================

if (basename($_SERVER['SCRIPT_FILENAME']) !== 'functions.php') {
    return; // Included via require_once — stop here and do not redirect
}

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    header("Location: ../views/register.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ============================================
// Register
// ============================================
if ($action === 'register') {

    $full_name        = trim($_POST['full_name']        ?? '');
    $email            = trim($_POST['email']            ?? '');
    $password         = trim($_POST['password']         ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    $errors = [];

    if (empty($full_name))               $errors[] = "Full name is required";
    if (empty($email))                   $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email is not valid";
    if (empty($password))                $errors[] = "Password is required";
    elseif (strlen($password) < 6)       $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "This email is already registered";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$full_name, $email, $hashed_password]);
        header("Location: ../views/login.php?success=1");
        exit();
    }

    $_SESSION['errors']    = $errors;
    $_SESSION['old_name']  = $full_name;
    $_SESSION['old_email'] = $email;
    header("Location: ../views/register.php");
    exit();
}

// ============================================
// Login
// ============================================
if ($action === 'login') {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    $errors = [];

    if (empty($email))    $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = "Email or password is incorrect";
        }
    }

    if (empty($errors)) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    }

    $_SESSION['errors']    = $errors;
    $_SESSION['old_email'] = $email;
    header("Location: ../views/login.php");
    exit();
}

// ============================================
// Buy
// ============================================
if ($action === 'buy') {

    // Buy does not require Login — anyone can purchase
    $book_id   = $_POST['book_id']   ?? null;
    $quantity  = $_POST['quantity']  ?? 1;
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $city      = trim($_POST['city']      ?? '');

    // user_id is optional — if logged in we store it, otherwise NULL
    $user_id = $_SESSION['user_id'] ?? null;

    $errors = [];

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security error. Please try again.";
    }

    if (!$book_id)        $errors[] = "الكتاب غير موجود";
    if ($quantity < 1)    $errors[] = "الكمية يجب أن تكون 1 على الأقل";
    if (empty($full_name)) $errors[] = "الاسم الكامل مطلوب";
    if (empty($phone))    $errors[] = "رقم الهاتف مطلوب";
    if (empty($city))     $errors[] = "المدينة مطلوبة";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if (!$book)                             $errors[] = "الكتاب غير موجود";
        if ($book && $quantity > $book['stock']) $errors[] = "الكمية المطلوبة غير متوفرة";
    }

    if (empty($errors)) {
        $total_price = $book['price_buy'] * $quantity;

        // Save the purchase — full_name, phone, and city in the purchases table
        $stmt = $pdo->prepare("
            INSERT INTO purchases (user_id, book_id, quantity, total_price, full_name, phone, city)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $book_id, $quantity, $total_price, $full_name, $phone, $city]);
        $purchase_id = $pdo->lastInsertId();
        $_SESSION['last_purchase_id'] = $purchase_id;

        // Decrease stock
        $stmt = $pdo->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $book_id]);

        header("Location: ../views/order_confirmation.php?type=buy&id={$purchase_id}");
        exit();
    }

    $_SESSION['errors'] = $errors;
    header("Location: ../views/buy.php?id={$book_id}");
    exit();
}

// ============================================
// Rent
// ============================================
if ($action === 'rent') {

    // Rent requires login — verify here again
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../views/login.php");
        exit();
    }

    $book_id   = $_POST['book_id']   ?? null;
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $duration  = (int)($_POST['duration'] ?? 0);

    $errors = [];

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security error. Please try again.";
    }

    if (!$book_id)         $errors[] = "الكتاب غير موجود";
    if (empty($full_name)) $errors[] = "الاسم الكامل مطلوب";
    if (empty($email))     $errors[] = "البريد الإلكتروني مطلوب";
    if (empty($phone))     $errors[] = "رقم الهاتف مطلوب";
    if ($duration < 1)     $errors[] = "يجب اختيار مدة الكراء";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if (!$book)             $errors[] = "الكتاب غير موجود";
        if ($book && $book['stock'] < 1) $errors[] = "الكتاب غير متوفر";
    }

    if (empty($errors)) {
        // Save rent data in session and redirect to payment page
        $_SESSION['rent_data'] = [
            'book_id'   => $book_id,
            'full_name' => $full_name,
            'email'     => $email,
            'phone'     => $phone,
            'duration'  => $duration,
        ];

        header("Location: ../views/payment.php");
        exit();
    }

    $_SESSION['errors'] = $errors;
    header("Location: ../views/rent.php?id={$book_id}");
    exit();
}

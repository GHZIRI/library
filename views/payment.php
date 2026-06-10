<?php
// payment.php — Payment page
// The user must be logged in
require_once '../core/functions.php';

// If not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verify that the rent data exists in the session
if (!isset($_SESSION['rent_data'])) {
    header("Location: catalogue.php");
    exit();
}

$rent_data = $_SESSION['rent_data'];
$book_id = $rent_data['book_id'];

// Fetch book data
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: catalogue.php");
    exit();
}

// If the payment form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve card details
    $card_holder = trim($_POST['card_holder'] ?? '');
    $card_number = trim($_POST['card_number'] ?? '');
    $expiry      = trim($_POST['expiry']      ?? '');
    $cvv         = trim($_POST['cvv']         ?? '');

    $errors = [];

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Security error. Please try again.";
    }

    // Basic validation of fields
    if (empty($card_holder)) $errors[] = "اسم حامل البطاقة مطلوب";
    if (strlen($card_number) !== 16 || !is_numeric($card_number)) $errors[] = "رقم البطاقة يجب أن يكون 16 رقماً";
    if (empty($expiry))      $errors[] = "تاريخ الانتهاء مطلوب";
    if (strlen($cvv) < 3)    $errors[] = "CVV غير صحيح";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT stock FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $current_book = $stmt->fetch();

        if (!$current_book || $current_book['stock'] < 1) {
            $errors[] = "This book is no longer available.";
        }
    }

    if (empty($errors)) {
        // Payment succeeded — register the Rent in the database
        $user_id    = $_SESSION['user_id'];
        $full_name  = $rent_data['full_name'];
        $email      = $rent_data['email'];
        $phone      = $rent_data['phone'];
        $duration   = (int)$rent_data['duration'];
        $rent_from  = date('Y-m-d');
        $rent_until = date('Y-m-d', strtotime("+{$duration} days"));
        $total_price = $book['price_rent'] * $duration;

        // Insert record into rentals table with paid = 1
        $stmt = $pdo->prepare("
            INSERT INTO rentals (user_id, book_id, full_name, email, phone, rent_from, rent_until, total_price, paid)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$user_id, $book_id, $full_name, $email, $phone, $rent_from, $rent_until, $total_price]);
        $rental_id = $pdo->lastInsertId();

        // Decrease stock
        $stmt = $pdo->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?");
        $stmt->execute([$book_id]);

        // Remove rent data from session
        unset($_SESSION['rent_data']);

        // Redirect to confirmation page
        header("Location: order_confirmation.php?type=rent&id={$rental_id}");
        exit();
    }
}

$errors = $errors ?? [];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدفع - Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <h1>📚 Library</h1>
    <div class="nav-links">
        <a href="catalogue.php">الكتالوج</a>
        <a href="user_dashboard.php">حسابي</a>
        <a href="../core/logout.php">خروج</a>
    </div>
</nav>

<div class="buy-container">

    <h2>💳 الدفع</h2>

    <!-- Order summary -->
    <div class="book-summary">
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <p>المستأجر: <strong><?= htmlspecialchars($rent_data['full_name']) ?></strong></p>
        <p>مدة الكراء: <strong><?= $rent_data['duration'] ?> أيام</strong></p>
        <p>السعر الإجمالي: <strong><?= $book['price_rent'] * $rent_data['duration'] ?> درهم</strong></p>
    </div>

    <!-- Error messages -->
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Payment form -->
    <form action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <!-- Cardholder Name -->
        <div class="form-group">
            <label>اسم حامل البطاقة *</label>
            <input type="text" name="card_holder"
                   placeholder="الاسم كما يظهر في البطاقة"
                   value="<?= htmlspecialchars($_POST['card_holder'] ?? $rent_data['full_name']) ?>"
                   required>
        </div>

        <!-- Card Number -->
        <div class="form-group">
            <label>رقم البطاقة * (16 رقم)</label>
            <input type="text" name="card_number"
                   placeholder="1234 5678 9012 3456"
                   maxlength="16"
                   pattern="[0-9]{16}"
                   title="أدخل 16 رقماً بدون مسافات"
                   required>
        </div>

        <!-- Expiration date and CVV in the same row -->
        <div style="display: flex; gap: 16px;">
            <div class="form-group" style="flex: 1;">
                <label>تاريخ الانتهاء *</label>
                <input type="month" name="expiry" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>CVV *</label>
                <input type="text" name="cvv"
                       placeholder="123"
                       maxlength="4"
                       pattern="[0-9]{3,4}"
                       title="أدخل 3 أو 4 أرقام"
                       required>
            </div>
        </div>

        <button type="submit" class="btn-primary">✅ تأكيد الدفع وإتمام الكراء</button>
    </form>

    <a href="catalogue.php" class="btn-back">⬅ العودة للكتالوج</a>

</div>

</body>
</html>

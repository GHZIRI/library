<?php
// rent.php — The user must be logged in
require_once '../core/functions.php';

// If not logged in, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the book ID from the URL
$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    header("Location: catalogue.php");
    exit();
}

// Fetch book data
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: catalogue.php");
    exit();
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get errors from the session if they exist
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كراء - <?= htmlspecialchars($book['title']) ?></title>
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

<div class="rent-container">

    <h2>📖 كراء كتاب</h2>

    <!-- Book information -->
    <div class="book-summary">
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <p><?= htmlspecialchars($book['author']) ?></p>
        <p>السعر اليومي: <strong><?= $book['price_rent'] ?> درهم/يوم</strong></p>
        <p>المخزون: <strong><?= $book['stock'] ?></strong></p>
    </div>

    <!-- Error messages -->
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($book['stock'] > 0): ?>

        <form action="../core/functions.php" method="POST">
            <input type="hidden" name="action"  value="rent">
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <!-- Full name — auto-filled from the account -->
            <div class="form-group">
                <label>الاسم الكامل *</label>
                <input type="text" name="full_name"
                       value="<?= htmlspecialchars($user['full_name']) ?>"
                       placeholder="أدخل اسمك الكامل" required>
            </div>

            <!-- Email — auto-filled from the account -->
            <div class="form-group">
                <label>البريد الإلكتروني *</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       placeholder="example@email.com" required>
            </div>

            <!-- Phone number -->
            <div class="form-group">
                <label>رقم الهاتف *</label>
                <input type="text" name="phone" placeholder="مثال: 0612345678" required>
            </div>

            <!-- Rent duration — dropdown selection -->
            <div class="form-group">
                <label>مدة الكراء *</label>
                <select name="duration" id="duration" required>
                    <option value="">-- اختر المدة --</option>
                    <option value="3">3 أيام</option>
                    <option value="7">أسبوع (7 أيام)</option>
                    <option value="14">أسبوعان (14 يوم)</option>
                    <option value="30">شهر (30 يوم)</option>
                </select>
            </div>

            <!-- Total price calculation display -->
            <div class="price-calculator">
                <p>السعر اليومي: <strong><?= $book['price_rent'] ?> درهم</strong></p>
                <p>السعر الإجمالي: <strong id="total-price">0 درهم</strong></p>
            </div>

            <button type="submit" class="btn-primary">➡ المتابعة للدفع</button>
        </form>

    <?php else: ?>
        <p class="out-stock">❌ هذا الكتاب غير متوفر حالياً.</p>
    <?php endif; ?>

    <a href="catalogue.php" class="btn-back">⬅ العودة للكتالوج</a>

</div>

<script>
    // Automatically calculate the total price when selecting the duration
    const pricePerDay = <?= $book['price_rent'] ?>;
    const durationSelect = document.getElementById('duration');
    const totalDiv = document.getElementById('total-price');

    durationSelect.addEventListener('change', function () {
        const days = parseInt(this.value) || 0;
        totalDiv.textContent = (days * pricePerDay) + ' درهم';
    });
</script>

</body>
</html>

<?php
// buy.php — The user does not need to be logged in
require_once '../core/functions.php';

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

// Get errors from the session if they exist
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شراء - <?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <h1>📚 Library</h1>
    <div class="nav-links">
        <a href="catalogue.php">الكتالوج</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="user_dashboard.php">حسابي</a>
            <a href="../core/logout.php">خروج</a>
        <?php else: ?>
            <a href="login.php">دخول</a>
        <?php endif; ?>
    </div>
</nav>

<div class="buy-container">

    <h2>🛒 شراء كتاب</h2>

    <!-- Book information -->
    <div class="book-summary">
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <p><?= htmlspecialchars($book['author']) ?></p>
        <p>السعر: <strong><?= $book['price_buy'] ?> درهم</strong></p>
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
            <input type="hidden" name="action"  value="buy">
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <!-- Full name -->
            <div class="form-group">
                <label>الاسم الكامل *</label>
                <input type="text" name="full_name" placeholder="أدخل اسمك الكامل" required>
            </div>

            <!-- Phone number -->
            <div class="form-group">
                <label>رقم الهاتف *</label>
                <input type="text" name="phone" placeholder="مثال: 0612345678" required>
            </div>

            <!-- City -->
            <div class="form-group">
                <label>المدينة *</label>
                <input type="text" name="city" placeholder="أدخل مدينتك" required>
            </div>

            <!-- Quantity -->
            <div class="form-group">
                <label>الكمية</label>
                <input type="number" name="quantity" min="1" max="<?= $book['stock'] ?>" value="1" required>
            </div>

            <button type="submit" class="btn-primary">✅ تأكيد الشراء</button>
        </form>

    <?php else: ?>
        <p class="out-stock">❌ هذا الكتاب غير متوفر حالياً.</p>
    <?php endif; ?>

    <a href="catalogue.php" class="btn-back">⬅ العودة للكتالوج</a>

</div>

</body>
</html>

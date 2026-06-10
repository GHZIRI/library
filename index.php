<?php
session_start();
require_once 'core/db.php';

$stmt = $pdo->prepare("SELECT * FROM books ORDER BY created_at DESC");
$stmt->execute();
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 الكتالوج — مكتبة الأندلس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/shared.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">📚 مكتبة الأندلس</div>
    <div class="nav-links">
        <a href="index.php" class="active">الرئيسية</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="views/user_dashboard.php">حسابي</a>
            <a href="core/logout.php">تسجيل الخروج</a>
        <?php else: ?>
            <a href="views/login.php">تسجيل الدخول</a>
        <?php endif; ?>
    </div>
</nav>

<main class="books-section">
    <h2 class="section-title">📚 قائمة الكتب المتاحة</h2>

    <?php if (empty($books)): ?>
        <p>لا توجد كتب حالياً.</p>
    <?php else: ?>
        <div class="books-grid">
            <?php foreach ($books as $book): ?>
                <div class="book-card">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="assets/images/<?= htmlspecialchars($book['cover_image']) ?>" class="book-cover">
                    <?php else: ?>
                        <div class="book-cover-placeholder">📚</div>
                    <?php endif; ?>

                    <div class="book-info">
                        <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
                        <p class="book-author">✍️ <?= htmlspecialchars($book['author']) ?></p>
                        <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>
                        <p class="book-desc"><?= mb_substr($book['description'], 0, 100) ?>...</p>
                        <div class="book-prices">
                            <span class="price-buy">🛍️ شراء: <?= $book['price_buy'] ?> درهم</span>
                            <span class="price-rent">📅 كراء: <?= $book['price_rent'] ?> درهم/يوم</span>
                        </div>
                    </div>

                    <div class="book-actions">
                        <a href="read.php?id=<?= $book['id'] ?>" class="btn btn-read">📖 اقرأ</a>
                        <?php if ($book['stock'] > 0): ?>
                            <a href="buy.php?id=<?= $book['id'] ?>" class="btn btn-buy">🛍️ شراء</a>
                            <a href="rent.php?id=<?= $book['id'] ?>" class="btn btn-rent">📅 كراء</a>
                        <?php else: ?>
                            <span class="btn-disabled">نفذ المخزون</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer class="footer">
    <p>📚 جميع الحقوق محفوظة لمكتبة الأندلس © <?= date('Y') ?></p>
</footer>

</body>
</html>
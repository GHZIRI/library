<?php
/*
 * ============================================================
 * الملف: read.php
 * الوظيفة: صفحة قراءة الكتاب
 * ============================================================
 *
 * كيف يشتغل؟
 * 1. نجيب رقم الكتاب من الرابط (?id=3)
 * 2. نبحث عنه في قاعدة البيانات
 * 3. نعرض محتواه (النص الكامل)
 *
 * مثال الرابط: read.php?id=1
 * ============================================================
 */

// --- نتصل بقاعدة البيانات ---
require_once 'core/db.php';

// --- خطوة 1: نجيبوا رقم الكتاب من الرابط ---
// $_GET['id'] = الرقم اللي جاء من الرابط
// ?? null = إذا ما جاش رقم، نضع null
$book_id = $_GET['id'] ?? null;

// --- خطوة 2: إذا ما كانش رقم، نرجعوا للصفحة الرئيسية ---
if (!$book_id) {
    header("Location: index.php");
    exit();
}

// --- خطوة 3: نبحث عن الكتاب في قاعدة البيانات ---
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(); // fetch = نجيب نتيجة واحدة فقط

// --- خطوة 4: إذا ما لقيناش الكتاب، نرجعوا للرئيسية ---
if (!$book) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📖 <?= htmlspecialchars($book['title']) ?> — قراءة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- الشريط العلوي -->
<nav class="navbar">
    <div class="nav-brand">📚 مكتبتي</div>
    <div class="nav-links">
        <a href="index.php">← الرئيسية</a>
    </div>
</nav>

<!-- ============================================================
     صفحة القراءة
     ============================================================ -->
<main class="read-page">

    <!-- رأس الصفحة — معلومات الكتاب -->
    <div class="read-header">
        <div class="read-header-info">
            <span class="book-category-tag"><?= htmlspecialchars($book['category']) ?></span>
            <h1 class="read-title"><?= htmlspecialchars($book['title']) ?></h1>
            <p class="read-author">✍️ تأليف: <strong><?= htmlspecialchars($book['author']) ?></strong></p>
            <p class="read-desc"><?= htmlspecialchars($book['description'] ?? '') ?></p>
        </div>

        <!-- أزرار الإجراء في رأس الصفحة -->
        <div class="read-header-actions">
            <a href="buy.php?id=<?= $book['id'] ?>" class="btn btn-buy">
                🛍️ اشترِ هذا الكتاب — <?= $book['price_buy'] ?> درهم
            </a>
            <a href="rent.php?id=<?= $book['id'] ?>" class="btn btn-rent">
                📅 استأجر هذا الكتاب — <?= $book['price_rent'] ?> درهم/يوم
            </a>
        </div>
    </div>

    <!-- ============================================================
         محتوى الكتاب — النص الكامل
         ============================================================ -->
    <div class="read-content-box">

        <?php if (!empty($book['content'])): ?>

            <!-- نعرض محتوى الكتاب —
                 nl2br = يحول سطر جديد (\n) إلى <br> في HTML
                 htmlspecialchars = يحمينا من أكواد خطيرة
            -->
            <div class="read-content">
                <?= nl2br(htmlspecialchars($book['content'])) ?>
            </div>

        <?php else: ?>
            <!-- إذا ما كانش محتوى -->
            <div class="no-content">
                <p>📭 محتوى هذا الكتاب غير متوفر للقراءة حالياً.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- أزرار أسفل الصفحة -->
    <div class="read-footer-actions">
        <a href="index.php" class="btn btn-back">← العودة للرئيسية</a>
        <?php if ($book['stock'] > 0): ?>
            <a href="buy.php?id=<?= $book['id'] ?>" class="btn btn-buy">🛍️ اشترِ</a>
            <a href="rent.php?id=<?= $book['id'] ?>" class="btn btn-rent">📅 استأجر</a>
        <?php endif; ?>
    </div>

</main>

<!-- Footer -->
<footer class="footer">
    <p>📚 مكتبتي — جميع الحقوق محفوظة <?= date('Y') ?></p>
</footer>

</body>
</html>

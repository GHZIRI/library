<?php
/*
 * ============================================================
 * الملف: index.php
 * الوظيفة: الصفحة الرئيسية — تعرض قائمة الكتب
 * ============================================================
 *
 * كيف يشتغل هذا الملف؟
 * 1. نتصل بقاعدة البيانات (db.php)
 * 2. نجيب جميع الكتب من الجدول
 * 3. نعرضها في الصفحة كارت كارت
 *
 * كل كتاب فيه 3 أزرار:
 *   - Read  → يفتح صفحة قراءة الكتاب
 *   - Buy   → يفتح فورم الشراء
 *   - Rent  → يفتح فورم الكراء
 * ============================================================
 */

// --- خطوة 1: نربطوا مع قاعدة البيانات ---
// هذا الملف فيه معلومات الاتصال بـ MySQL
require_once 'core/db.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- عنوان الصفحة اللي تظهر في التبويب -->
    <title>📚 مكتبة — الصفحة الرئيسية</title>

    <!-- خط جميل من جوجل -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    <!-- ملف CSS الخاص بالصفحة الرئيسية -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- ============================================================
     الـ NAVBAR — الشريط العلوي للتنقل
     ============================================================ -->
<nav class="navbar">
    <!-- اسم الموقع -->
    <div class="nav-brand">📚 مكتبتي</div>

    <!-- روابط التنقل -->
    <div class="nav-links">
        <a href="index.php" class="active">الرئيسية</a>
        <a href="views/login.php">تسجيل الدخول</a>
    </div>
</nav>

<!-- ============================================================
     قسم الترحيب — Hero Section
     ============================================================ -->
<section class="hero">
    <div class="hero-content">
        <h1>مرحباً بك في مكتبتنا 📖</h1>
        <p>اقرأ، اشترِ، أو استأجر الكتاب الذي تريده بسهولة تامة</p>
    </div>
</section>

<!-- ============================================================
     قائمة الكتب — Books Grid
     ============================================================ -->
<main class="books-section">
    <h2 class="section-title">📚 الكتب المتاحة</h2>

    <!-- --- خطوة 2: نجيبوا الكتب من قاعدة البيانات --- -->
    <?php
    // prepare = نحضر السؤال
    // execute = نرسلوا لقاعدة البيانات
    // fetchAll = نجيب جميع النتائج
    $stmt = $pdo->prepare("SELECT * FROM books ORDER BY created_at DESC");
    $stmt->execute();
    $books = $stmt->fetchAll();
    ?>

    <!-- نتحقق: كاين كتب؟ -->
    <?php if (empty($books)): ?>
        <p class="no-books">لا توجد كتب في المكتبة حالياً.</p>

    <?php else: ?>

        <!-- الشبكة اللي تعرض الكتب -->
        <div class="books-grid">

            <!-- --- خطوة 3: ندورو على كل كتاب ونعرضوه --- -->
            <?php foreach ($books as $book): ?>

                <!-- كارت الكتاب الواحد -->
                <div class="book-card">

                    <!-- صورة الكتاب (إذا موجودة) -->
                    <?php if (!empty($book['cover_image'])): ?>
                        <img
                            src="assets/images/<?= htmlspecialchars($book['cover_image']) ?>"
                            alt="غلاف <?= htmlspecialchars($book['title']) ?>"
                            class="book-cover"
                        >
                    <?php else: ?>
                        <!-- إذا ما كانتش صورة، نعرض إيموجي -->
                        <div class="book-cover-placeholder">📚</div>
                    <?php endif; ?>

                    <!-- معلومات الكتاب -->
                    <div class="book-info">
                        <!-- العنوان -->
                        <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>

                        <!-- المؤلف -->
                        <p class="book-author">✍️ <?= htmlspecialchars($book['author']) ?></p>

                        <!-- التصنيف -->
                        <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>

                        <!-- وصف قصير — نعرض فقط أول 80 حرف -->
                        <p class="book-desc">
                            <?= htmlspecialchars(mb_substr($book['description'] ?? '', 0, 80)) ?>...
                        </p>

                        <!-- الثمن -->
                        <div class="book-prices">
                            <span class="price-buy">🛍️ شراء: <?= $book['price_buy'] ?> درهم</span>
                            <span class="price-rent">📅 كراء: <?= $book['price_rent'] ?> درهم/يوم</span>
                        </div>

                        <!-- المخزون -->
                        <?php if ($book['stock'] > 0): ?>
                            <p class="in-stock">✅ متوفر (<?= $book['stock'] ?> نسخة)</p>
                        <?php else: ?>
                            <p class="out-stock">❌ غير متوفر</p>
                        <?php endif; ?>
                    </div>

                    <!-- أزرار الإجراء — Read / Buy / Rent -->
                    <div class="book-actions">

                        <!-- زر Read — يفتح صفحة القراءة -->
                        <a href="read.php?id=<?= $book['id'] ?>" class="btn btn-read">
                            📖 اقرأ
                        </a>

                        <!-- زر Buy — يفتح فورم الشراء (فقط إذا في مخزون) -->
                        <?php if ($book['stock'] > 0): ?>
                            <a href="buy.php?id=<?= $book['id'] ?>" class="btn btn-buy">
                                🛍️ اشترِ
                            </a>

                            <!-- زر Rent — يفتح فورم الكراء -->
                            <a href="rent.php?id=<?= $book['id'] ?>" class="btn btn-rent">
                                📅 استأجر
                            </a>
                        <?php else: ?>
                            <!-- إذا ما كانش مخزون، نعرض رسالة -->
                            <span class="btn btn-disabled">📦 نفذ المخزون</span>
                        <?php endif; ?>

                    </div>
                </div>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</main>

<!-- ============================================================
     Footer — أسفل الصفحة
     ============================================================ -->
<footer class="footer">
    <p>📚 مكتبتي — جميع الحقوق محفوظة <?= date('Y') ?></p>
</footer>

</body>
</html>

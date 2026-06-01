<?php
/**
 * صفحة الفهرس - الصفحة الرئيسية
 * 
 * تعرض جميع الكتب المتاحة مع إمكانية البحث والفلتر
 */

require_once '../core/functions.php';

// الحصول على البيانات من الـ URL
$search = sanitize($_GET['search'] ?? '');
$type_id = sanitize($_GET['type_id'] ?? '');

// الحصول على الكتب
$books = getAllBooks($search, $type_id);
$types = getAllTypes();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مكتبة - الفهرس</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 مكتبة</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">الرئيسية</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="user_dashboard.php">حسابي</a></li>
                    <li><a href="../core/logout.php">تسجيل الخروج</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">دخول</a></li>
                <?php endif; ?>
                <li><a href="../admin/login.php">دخول الأدمين</a></li>
            </ul>
        </div>
    </nav>

    <!-- العنوان الرئيسي -->
    <div class="container">
        <div class="hero">
            <h1>📖 مرحباً بك في مكتبتنا</h1>
            <p>اكتشف أفضل الروايات واختر ما يناسبك</p>
        </div>
    </div>

    <!-- شريط البحث والفلتر -->
    <div class="container">
        <form method="GET" action="catalogue.php" class="search-filter">
            <!-- حقل البحث -->
            <input 
                type="text" 
                name="search" 
                placeholder="ابحث عن كتاب أو مؤلف..." 
                value="<?php echo $search; ?>">

            <!-- قائمة الفلتر -->
            <select name="type_id">
                <option value="">جميع الأنواع</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?php echo $type['type_id']; ?>" 
                        <?php echo ($type_id == $type['type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['type_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- أزرار البحث -->
            <button type="submit" class="btn btn-primary">🔍 بحث</button>
            <a href="catalogue.php" class="btn btn-secondary">مسح</a>
        </form>
    </div>

    <!-- شبكة الكتب -->
    <div class="container">
        <?php if (empty($books)): ?>
            <div style="text-align: center; padding: 60px 20px; background-color: white; border-radius: 10px; margin: 20px 0;">
                <p style="font-size: 48px;">📭</p>
                <p style="font-size: 18px; font-weight: 700; color: var(--dark); margin: 10px 0;">لم نجد نتائج</p>
                <p style="color: var(--gray);">حاول تغيير معايير البحث</p>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <!-- صورة الكتاب -->
                        <div class="book-card-image">
                            <?php if (!empty($book['cover_image'])): ?>
                                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <?php else: ?>
                                📖
                            <?php endif; ?>
                        </div>

                        <!-- معلومات الكتاب -->
                        <div class="book-card-body">
                            <h3 class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-card-author">✍️ <?php echo htmlspecialchars($book['author']); ?></p>
                            <span class="book-card-type"><?php echo htmlspecialchars($book['type_name']); ?></span>
                            <p class="book-card-price">💰 <?php echo formatPrice($book['price_buy']); ?></p>

                            <!-- الأزرار -->
                            <div class="book-card-actions">
                                <form method="GET" action="buy.php" style="flex: 1;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">🛒 شراء</button>
                                </form>
                                <?php if ($book['available_rental']): ?>
                                    <form method="GET" action="rent.php" style="flex: 1;">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <button type="submit" class="btn btn-secondary" style="width: 100%;">📖 كراء</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- التذييل -->
    <footer class="footer">
        <p>&copy; 2026 مكتبة. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>

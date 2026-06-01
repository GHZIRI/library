<?php
/**
 * لوحة المستخدم
 * 
 * عرض طلبات المستخدم السابقة وملفه الشخصي
 */

require_once '../core/functions.php';

// فرض تسجيل الدخول
requireLogin();

$user_id = getCurrentUserId();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حسابي</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 مكتبة</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">الرئيسية</a></li>
                <li><a href="../core/logout.php">تسجيل الخروج</a></li>
            </ul>
        </div>
    </nav>

    <!-- محتوى الصفحة -->
    <div class="container">
        <h1 style="margin: 40px 0;">👤 لوحة التحكم</h1>

        <!-- قائمة التنقل -->
        <ul style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid var(--light); padding-bottom: 10px;">
            <li><a href="#orders" style="color: var(--primary); text-decoration: none; font-weight: 600; padding: 10px 20px; border-bottom: 3px solid var(--primary);">📦 طلباتي</a></li>
            <li><a href="catalogue.php" style="color: var(--gray); text-decoration: none; padding: 10px 20px;">🛒 تصفح الكتب</a></li>
        </ul>

        <!-- قسم الطلبات -->
        <div id="orders" style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 20px;">📦 طلبات الشراء والكراء</h2>

            <p style="color: var(--gray); margin-bottom: 20px;">
                📝 هنا يمكنك تتبع طلبات الشراء والكراء السابقة
            </p>

            <!-- قائمة فارغة -->
            <div style="text-align: center; padding: 40px; background-color: var(--light); border-radius: 10px;">
                <p style="font-size: 50px; margin-bottom: 15px;">📭</p>
                <p style="color: var(--gray); font-size: 16px;">لا توجد طلبات حتى الآن</p>
                <a href="catalogue.php" class="btn btn-primary" style="margin-top: 15px;">🛒 ابدأ التسوق الآن</a>
            </div>
        </div>
    </div>

    <!-- التذييل -->
    <footer class="footer">
        <p>&copy; 2026 مكتبة. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>

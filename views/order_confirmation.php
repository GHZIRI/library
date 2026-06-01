<?php
/**
 * صفحة تأكيد الطلب
 * 
 * تظهر بعد الشراء أو الكراء بنجاح
 */

require_once '../core/functions.php';
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد الطلب</title>
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
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- محتوى الصفحة -->
    <div class="container">
        <div style="max-width: 600px; margin: 60px auto; background-color: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <!-- الأيقونة -->
            <p style="font-size: 80px; margin-bottom: 20px;">✅</p>

            <!-- الرسالة الرئيسية -->
            <h1 style="color: var(--success); font-size: 28px; margin-bottom: 15px;">تم تأكيد الطلب!</h1>

            <!-- التفاصيل -->
            <p style="color: var(--gray); font-size: 16px; line-height: 1.8; margin-bottom: 30px;">
                شكراً لك على استخدامك لخدماتنا. لقد تم استقبال طلبك بنجاح وجاري معالجته. سنتواصل معك قريباً برسالة تأكيد.
            </p>

            <!-- معلومات إضافية -->
            <div style="background-color: var(--light); padding: 20px; border-radius: 5px; margin-bottom: 30px;">
                <p style="color: var(--gray); margin-bottom: 10px;">📋 <strong>رقم الطلب:</strong> #<?php echo date('YmdHis'); ?></p>
                <p style="color: var(--gray); margin-bottom: 10px;">📅 <strong>التاريخ:</strong> <?php echo formatDate(date('Y-m-d H:i:s')); ?></p>
                <p style="color: var(--gray);">⏳ <strong>الحالة:</strong> قيد المعالجة</p>
            </div>

            <!-- الأزرار -->
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="catalogue.php" class="btn btn-primary">🏠 العودة للرئيسية</a>
                <?php if (isLoggedIn()): ?>
                    <a href="user_dashboard.php" class="btn btn-secondary">👤 حسابي</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- التذييل -->
    <footer class="footer">
        <p>&copy; 2026 مكتبة. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>

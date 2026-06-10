<?php
session_start();
require_once '../core/functions.php';

if (isLoggedIn()) {
    redirect('catalogue.php');
}

$error = getFlash('error');
$success = getFlash('success');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب — مكتبة الأندلس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">📚 مكتبة الأندلس</div>
    <div class="nav-links">
        <a href="../index.php">الرئيسية</a>
        <a href="login.php">تسجيل الدخول</a>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-card">
        <h2>إنشاء حساب جديد</h2>
        <p class="subtitle">انضم إلينا واستمتع بمميزات القراءة والكراء</p>

        <?php if($error): ?>
            <div class="error-list">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div style="background: #dcfce7; color: #15803d; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="../core/functions.php" method="POST" class="auth-form">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="form-group">
                <label>الاسم الكامل</label>
                <input type="text" name="name" placeholder="مثال: محمد السالم" required>
            </div>

            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" placeholder="example@email.com" required>
            </div>

            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" placeholder="6 رموز على الأقل" required>
            </div>

            <div class="form-group">
                <label>تأكيد كلمة المرور</label>
                <input type="password" name="confirm_password" placeholder="أعد كتابة كلمة المرور" required>
            </div>

            <button type="submit" class="btn btn-primary btn-auth">إنشاء الحساب</button>
        </form>

        <div class="auth-footer">
            <p>لديك حساب بالفعل؟ <a href="login.php">سجل دخولك هنا</a></p>
        </div>
    </div>
</div>

<footer class="footer">
    <p>📚 جميع الحقوق محفوظة لمكتبة الأندلس © <?= date('Y') ?></p>
</footer>

</body>
</html>
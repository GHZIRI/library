<?php
session_start();

if(isset($_SESSION['user_id'])){
    header("Location: catalogue.php");
    exit();
}

$errors = $_SESSION['errors'] ?? [];
$old_email = $_SESSION['old_email'] ?? '';

unset($_SESSION['errors']);
unset($_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — مكتبة الأندلس</title>
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
        <a href="register.php">إنشاء حساب</a>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-card">
        <h2>تسجيل الدخول</h2>
        <p class="subtitle">مرحباً بك مجدداً! يرجى إدخال بياناتك</p>

        <?php if(!empty($errors)): ?>
            <div class="error-list">
                <?php foreach($errors as $error): ?>
                    <p>⚠️ <?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="../core/functions.php" method="post" class="auth-form">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" value="<?= htmlspecialchars($old_email) ?>" placeholder="example@email.com" required>
            </div>

            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-auth">دخول</button>
        </form>

        <div class="auth-footer">
            <p>ليس لديك حساب؟ <a href="register.php">أنشئ حساباً جديداً</a></p>
        </div>
    </div>
</div>

<footer class="footer">
    <p>📚 جميع الحقوق محفوظة لمكتبة الأندلس © <?= date('Y') ?></p>
</footer>

</body>
</html>
<?php
session_start();
require_once '../core/db.php';

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = 'admin';
        header("Location: dashboard.php");
        exit();
    } else {
        $error = 'بيانات الدخول غير صحيحة أو لست مشرفاً.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول المشرفين — مكتبة الأندلس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body style="background: var(--color-dark);">

<div class="auth-container">
    <div class="auth-card">
        <div style="text-align: center; margin-bottom: 20px; font-size: 40px;">🔐</div>
        <h2>لوحة التحكم</h2>
        <p class="subtitle">يرجى تسجيل الدخول للوصول للإدارة</p>

        <?php if($error): ?>
            <div class="error-list">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>البريد الإلكتروني للإدارة</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-auth">دخول المشرفين</button>
        </form>
    </div>
</div>

</body>
</html>

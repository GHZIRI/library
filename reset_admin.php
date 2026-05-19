<?php
/**
 * reset_admin.php — يصلح باسورد الأدمين مباشرة في قاعدة البيانات
 * افتحه في المتصفح مرة واحدة فقط:
 * http://localhost/library/reset_admin.php
 */

$host   = 'localhost';
$dbname = 'library';
$user   = 'root';
$pass   = '';

// الباسورد الجديد اللي تريده
$newPassword = 'admin123';
$newEmail    = 'admin@library.com';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // توليد الـ Hash الصحيح من PHP نفسها
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);

    // هل المستخدم موجود؟
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$newEmail]);
    $existing = $stmt->fetch();

    if ($existing) {
        // موجود: حدّث الباسورد فقط
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hash, $newEmail]);
        $action = "✅ تم تحديث باسورد الأدمين";
    } else {
        // غير موجود: أنشئ الأدمين
        $stmt = $pdo->prepare("INSERT INTO users (name_user, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute(['Admin', $newEmail, $hash]);
        $action = "✅ تم إنشاء حساب الأدمين";
    }

    // تحقق نهائي
    $verify = password_verify($newPassword, $hash);

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إصلاح باسورد الأدمين</title>
    <style>
        body { font-family: monospace; background: #1e1e2e; color: #cdd6f4; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
        .box { background:#313244; padding:32px; border-radius:12px; max-width:540px; width:100%; }
        h2 { margin-top:0; color:#cba6f7; }
        .ok  { background:#a6e3a1; color:#1e1e2e; padding:12px 16px; border-radius:8px; margin:8px 0; font-weight:bold; }
        .err { background:#f38ba8; color:#1e1e2e; padding:12px 16px; border-radius:8px; margin:8px 0; font-weight:bold; }
        .info { background:#2a2a3e; padding:16px; border-radius:8px; margin-top:16px; line-height:2; }
        code { background:#45475a; padding:2px 8px; border-radius:4px; color:#f9e2af; }
        .btn { display:block; margin-top:20px; padding:12px; background:#6c63ff; color:#fff; text-align:center; border-radius:8px; text-decoration:none; font-size:16px; }
    </style>
</head>
<body>
<div class="box">
    <h2>🔧 إصلاح باسورد الأدمين</h2>

    <?php if (isset($error)): ?>
        <div class="err">❌ خطأ في قاعدة البيانات: <?= htmlspecialchars($error) ?></div>
        <div class="info">
            <strong>الأسباب المحتملة:</strong><br>
            - XAMPP مش شغّال<br>
            - قاعدة البيانات <code>library</code> غير موجودة<br>
            - شغّل أولاً ملف <code>core/script.sql</code> في phpMyAdmin
        </div>
    <?php else: ?>
        <div class="ok"><?= $action ?></div>
        <div class="ok">✅ التحقق password_verify: <?= $verify ? 'ناجح' : 'فاشل!' ?></div>

        <div class="info">
            <strong>بيانات الدخول:</strong><br>
            📧 Email: <code><?= $newEmail ?></code><br>
            🔑 Password: <code><?= $newPassword ?></code><br>
            👤 Role: <code>admin</code>
        </div>

        <a href="views/login.php" class="btn">🚀 اذهب لصفحة Login</a>

        <p style="margin-top:16px;color:#f38ba8;font-size:13px;">
            ⚠️ احذف هذا الملف بعد تسجيل الدخول!<br>
            (gen_hash.php و reset_admin.php)
        </p>
    <?php endif; ?>
</div>
</body>
</html>

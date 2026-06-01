<?php
/**
 * ملف الإعداد
 * 
 * إنشاء قاعدة البيانات والجداول والبيانات الأولية
 * يتم تشغيله مرة واحدة فقط
 */

// الاتصال المباشر بـ MySQL بدون تحديد قاعدة بيانات
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    die('❌ فشل الاتصال بـ MySQL: ' . htmlspecialchars($e->getMessage()));
}

// قراءة ملف SQL
$sql_file = file_get_contents(__DIR__ . '/core/script.sql');

if (!$sql_file) {
    die('❌ فشل في قراءة ملف script.sql');
}

// تقسيم الأوامر
$statements = array_filter(
    array_map('trim', explode(';', $sql_file)),
    fn($stmt) => !empty($stmt) && strpos($stmt, '/*') === false
);

$success = 0;
$errors = [];

// تنفيذ الأوامر
foreach ($statements as $statement) {
    try {
        $pdo->exec($statement);
        $success++;
    } catch (PDOException $e) {
        $errors[] = htmlspecialchars($e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد النظام</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div style="max-width: 600px; margin: 60px auto; background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            
            <h1 style="text-align: center; margin-bottom: 30px;">📚 إعداد المكتبة الإلكترونية</h1>

            <?php if (count($errors) === 0): ?>
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <p><strong>✅ تم الإعداد بنجاح!</strong></p>
                    <p>تم إنشاء قاعدة البيانات والجداول بنجاح.</p>
                </div>

                <div style="background-color: var(--light); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <p><strong>📊 الإحصائيات:</strong></p>
                    <ul style="list-style: none; padding: 0;">
                        <li>✓ عدد العمليات المنفذة: <strong><?php echo $success; ?></strong></li>
                        <li>✓ قاعدة البيانات: <strong>library</strong></li>
                        <li>✓ الجداول: books, book_types, users, orders_buy, orders_rental</li>
                    </ul>
                </div>

                <div style="background-color: var(--light); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <p><strong>🔐 بيانات الدخول التجريبية:</strong></p>
                    <ul style="list-style: none; padding: 0;">
                        <li>📧 البريد الإلكتروني (أدمين): <code>admin@library.com</code></li>
                        <li>🔑 كلمة السر: <code>admin123</code></li>
                    </ul>
                </div>

                <a href="index.php" class="btn btn-primary" style="display: block; text-align: center; padding: 12px;">🚀 ابدأ الآن</a>
            <?php else: ?>
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <p><strong>❌ حدثت أخطاء أثناء الإعداد</strong></p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <p style="color: var(--gray); text-align: center; margin-top: 20px;">
                    يرجى التأكد من أن MySQL يعمل بشكل صحيح وأن المستخدم 'root' موجود بدون كلمة سر.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
/**
 * ملف مؤقت لتوليد hash كلمة المرور
 * افتحه في المتصفح مرة واحدة فقط ثم احذفه
 * http://localhost/library/gen_hash.php
 */

$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<pre style='font-family:monospace;font-size:16px;background:#1e1e2e;color:#a6e3a1;padding:20px;border-radius:8px;'>";
echo "كلمة المرور الأصلية : admin123\n\n";
echo "الـ Hash المشفّر:\n";
echo $hash . "\n\n";
echo "--- انسخ هذا السطر وضعه في script.sql ---\n\n";
echo htmlspecialchars("('Admin', 'admin@library.com', '{$hash}', 'admin');");
echo "\n\n";
echo "--- للتحقق: password_verify تقول ---\n";
echo password_verify('admin123', $hash) ? "✅ الباسورد صحيح" : "❌ خطأ";
echo "</pre>";

<?php
/**
 * تسجيل خروج الأدمين
 * تدمير الجلسة والتوجيه للدخول
 */

// بدء الجلسة
session_start();

// تدمير جميع متغيرات الجلسة
session_destroy();

// تفريغ مصفوفة SESSION
$_SESSION = [];

// حذف كوكيز الجلسة (إذا كانت موجودة)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// توجيه المستخدم إلى صفحة الدخول
header('Location: login.php');
exit();
?>
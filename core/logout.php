<?php
/**
 * تسجيل الخروج
 * 
 * إنهاء الجلسة وحذف بيانات المستخدم
 */

require_once 'functions.php';

// تدمير الجلسة
session_destroy();

// الحذف الآمن للبيانات
$_SESSION = [];

// إعادة التوجيه
header('Location: ../admin/login.php');
exit;

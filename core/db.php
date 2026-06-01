<?php
/**
 * ملف الاتصال بقاعدة البيانات
 * 
 * هذا الملف ينشئ اتصال PDO آمن مع MySQL
 * يُستخدم في جميع ملفات PHP
 */

$host = 'localhost';
$dbname = 'library';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('خطأ في الاتصال بقاعدة البيانات: ' . htmlspecialchars($e->getMessage()));
}

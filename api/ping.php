<?php
// =============================================
// ملف اختبار بسيط — ping.php
// الهدف: نتأكد هل PHP يشتغل ويرجع JSON صح
// =============================================

// هذا السطر مهم جداً — يخبر المتصفح أن الرد JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ────────────────────────────────────────────
// اختبار 1: هل PHP يشتغل؟
// ────────────────────────────────────────────
$phpWorks = true;

// ────────────────────────────────────────────
// اختبار 2: هل قاعدة البيانات تتصل؟
// ────────────────────────────────────────────
$dbWorks   = false;
$dbError   = '';
$dbMessage = '';

$host     = 'localhost';
$dbname   = 'library';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password
    );
    $dbWorks   = true;
    $dbMessage = 'اتصال قاعدة البيانات ناجح ✅';
} catch (PDOException $e) {
    $dbError   = $e->getMessage();
    $dbMessage = 'فشل الاتصال ❌: ' . $e->getMessage();
}

// ────────────────────────────────────────────
// اختبار 3: هل جدول books موجود؟
// ────────────────────────────────────────────
$tableExists  = false;
$booksCount   = 0;
$tableMessage = '';

if ($dbWorks) {
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM books");
        $booksCount   = $result->fetchColumn();
        $tableExists  = true;
        $tableMessage = "جدول books موجود ✅ — عدد الكتب: {$booksCount}";
    } catch (PDOException $e) {
        $tableMessage = 'جدول books غير موجود ❌: ' . $e->getMessage();
    }
}

// ────────────────────────────────────────────
// اختبار 4: بيانات وهمية (Dummy Data)
// لنرى هل الـ JS يعرضها صح
// ────────────────────────────────────────────
$dummyBooks = [
    [
        'book_id'  => 1,
        'title'    => 'رواية اختبارية 1',
        'author'   => 'كاتب تجريبي',
        'price'    => 29.99,
        'category' => 'رواية',
        'cover'    => '',
    ],
    [
        'book_id'  => 2,
        'title'    => 'رواية اختبارية 2',
        'author'   => 'كاتبة تجريبية',
        'price'    => 19.99,
        'category' => 'شعر',
        'cover'    => '',
    ],
    [
        'book_id'  => 3,
        'title'    => 'كتاب تجريبي 3',
        'author'   => 'مؤلف مجهول',
        'price'    => 9.99,
        'category' => 'تاريخ',
        'cover'    => '',
    ],
];

// ────────────────────────────────────────────
// الرد النهائي
// ────────────────────────────────────────────
echo json_encode([
    'success'      => true,
    'ping'         => 'pong ✅ — PHP يشتغل!',
    'php_version'  => PHP_VERSION,
    'db_works'     => $dbWorks,
    'db_message'   => $dbMessage,
    'table_exists' => $tableExists,
    'table_msg'    => $tableMessage,
    'books_in_db'  => $booksCount,
    // هذه هي الـ items اللي main.js يبحث عنها
    'items'        => $dummyBooks,
    'total'        => count($dummyBooks),
    'count'        => count($dummyBooks),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

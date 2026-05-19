<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * SIMPLE TEST FILE - PHP API
 * اختبر هل الـ Server يرد بـ JSON صحيح؟ (بدون قاعدة بيانات)
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * استخدام:
 * 1. افتح في المتصفح مباشرة:
 *    http://localhost/library/api/test_books.php
 * 
 * 2. أو استدعه من JavaScript:
 *    fetch('../api/test_books.php')
 * 
 * يجب تشوف JSON بيانات وهمية (Dummy Data)
 */

// ════════════════════════════════════════════════════════════════════════════
// Step 1: قل للمتصفح أننا نرسل JSON
// ════════════════════════════════════════════════════════════════════════════
header('Content-Type: application/json; charset=utf-8');

// ════════════════════════════════════════════════════════════════════════════
// Step 2: بيانات وهمية (Dummy Data) - بدون قاعدة بيانات
// ════════════════════════════════════════════════════════════════════════════
$books = [
    [
        'id_book' => 1,
        'book_id' => 'test-1',
        'title' => '✅ Test Book 1',
        'author' => 'Test Author',
        'price_buy' => 25.00,
        'price_rental' => 3.50,
        'cover_image' => 'https://via.placeholder.com/150?text=Book+1'
    ],
    [
        'id_book' => 2,
        'book_id' => 'test-2',
        'title' => '✅ Test Book 2',
        'author' => 'Another Author',
        'price_buy' => 20.00,
        'price_rental' => 3.00,
        'cover_image' => 'https://via.placeholder.com/150?text=Book+2'
    ],
    [
        'id_book' => 3,
        'book_id' => 'test-3',
        'title' => '✅ Test Book 3',
        'author' => 'Third Author',
        'price_buy' => 22.00,
        'price_rental' => 3.25,
        'cover_image' => 'https://via.placeholder.com/150?text=Book+3'
    ]
];

// ════════════════════════════════════════════════════════════════════════════
// Step 3: بناء Response
// ════════════════════════════════════════════════════════════════════════════
$response = [
    'success' => true,  // ✅ دليل النجاح
    'items' => $books,   // ✅ البيانات الوهمية
    'count' => count($books),
    'message' => 'Test data - استخدم هذا فقط للاختبار',
    'test_info' => [
        'file' => 'api/test_books.php',
        'time' => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ]
];

// ════════════════════════════════════════════════════════════════════════════
// Step 4: أرسل Response
// ════════════════════════════════════════════════════════════════════════════
http_response_code(200);  // ✅ HTTP 200 = نجح
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// ════════════════════════════════════════════════════════════════════════════
// اذا وصلت هنا = كل شيء يعمل!
// ════════════════════════════════════════════════════════════════════════════
?>

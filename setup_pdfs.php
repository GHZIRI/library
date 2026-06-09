<?php
/*
 * ============================================================
 * الملف: setup_pdfs.php
 * الوظيفة: ينشئ ملفات PDF تجريبية في assets/pdfs/
 * ============================================================
 *
 * كيف تشغله؟
 * افتح في المتصفح: http://localhost/library/setup_pdfs.php
 *
 * يفعل هذا الملف مرة واحدة فقط:
 * - ينشئ مجلد assets/pdfs/
 * - ينشئ 8 ملفات PDF تجريبية
 * - بعد التشغيل، ضع ملفاتك الحقيقية في نفس المجلد
 *   باستعمال نفس الأسماء (book_01.pdf, book_02.pdf...)
 * ============================================================
 */

require_once 'core/db.php';

// --- دالة تنشئ ملف PDF صغير صالح ---
// هذه الدالة تبني ملف PDF من الصفر بدون مكتبات خارجية
function create_simple_pdf($book_title, $book_author, $save_path) {

    // نحول النص العربي لنص آمن — خطوط Type1 تدعم فقط ASCII
    // في مشروع حقيقي، استعمل FPDF أو TCPDF لدعم العربية
    $title_ascii  = transliterator_transliterate('Any-Latin; Latin-ASCII', $book_title)  ?? $book_title;
    $author_ascii = transliterator_transliterate('Any-Latin; Latin-ASCII', $book_author) ?? $book_author;

    // إذا فشل التحويل، استعمل اسم بسيط
    if (!preg_match('/^[\x20-\x7E]+$/', $title_ascii))  $title_ascii  = 'Arabic Book';
    if (!preg_match('/^[\x20-\x7E]+$/', $author_ascii)) $author_ascii = 'Arabic Author';

    // --- نبني محتوى الصفحة (PDF Content Stream) ---
    $stream  = "BT\n";                            // Begin Text
    $stream .= "/F1 22 Tf\n";                     // خط Helvetica حجم 22
    $stream .= "50 740 Td\n";                     // الموضع: 50 من اليسار، 740 من الأسفل
    $stream .= "(" . addcslashes($title_ascii, "()\\\r\n") . ") Tj\n"; // عنوان الكتاب
    $stream .= "0 -35 Td\n";                      // انزل 35 نقطة
    $stream .= "/F1 14 Tf\n";                     // خط أصغر
    $stream .= "(By: " . addcslashes($author_ascii, "()\\\r\n") . ") Tj\n";
    $stream .= "0 -50 Td\n";
    $stream .= "/F1 11 Tf\n";
    $stream .= "(This is a sample PDF for demonstration.) Tj\n";
    $stream .= "0 -20 Td\n";
    $stream .= "(Replace with the real book PDF file.) Tj\n";
    $stream .= "0 -20 Td\n";
    $stream .= "(File: " . addcslashes(basename($save_path), "()\\\r\n") . ") Tj\n";
    $stream .= "0 -60 Td\n";
    $stream .= "/F1 9 Tf\n";
    $stream .= "(--------- SAMPLE CONTENT ---------) Tj\n";
    $stream .= "0 -20 Td\n";
    $stream .= "(Lorem ipsum dolor sit amet, consectetur adipiscing elit.) Tj\n";
    $stream .= "0 -20 Td\n";
    $stream .= "(Sed do eiusmod tempor incididunt ut labore et dolore magna.) Tj\n";
    $stream .= "0 -20 Td\n";
    $stream .= "(Ut enim ad minim veniam, quis nostrud exercitation.) Tj\n";
    $stream .= "ET\n";                            // End Text
    $slen = strlen($stream);

    // --- نبني هيكل ملف PDF ---
    // كل PDF مكون من "objects" مرقمة
    $pdf = "%PDF-1.4\n";

    // نحفظ موضع كل object في الملف (لبناء xref table)
    $offsets = [];

    // Object 1: Catalog — فهرس رئيسي للملف
    $offsets[1] = strlen($pdf);
    $pdf .= "1 0 obj\n<</Type/Catalog/Pages 2 0 R>>\nendobj\n";

    // Object 2: Pages — قائمة الصفحات
    $offsets[2] = strlen($pdf);
    $pdf .= "2 0 obj\n<</Type/Pages/Kids[3 0 R]/Count 1>>\nendobj\n";

    // Object 3: Page — الصفحة الواحدة
    $offsets[3] = strlen($pdf);
    $pdf .= "3 0 obj\n<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]"
          . "/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>\nendobj\n";

    // Object 4: Content Stream — محتوى الصفحة
    $offsets[4] = strlen($pdf);
    $pdf .= "4 0 obj\n<</Length $slen>>\nstream\n{$stream}endstream\nendobj\n";

    // Object 5: Font — الخط المستخدم
    $offsets[5] = strlen($pdf);
    $pdf .= "5 0 obj\n<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>\nendobj\n";

    // --- XRef Table — جدول يخبر القارئ أين كل object ---
    $xref_pos = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 6\n";                                              // 6 objects (0 إلى 5)
    $pdf .= sprintf("%010d 65535 f\r\n", 0);                     // Object 0: free
    for ($i = 1; $i <= 5; $i++) {
        $pdf .= sprintf("%010d 00000 n\r\n", $offsets[$i]);      // Objects 1-5
    }

    // --- Trailer — نهاية الملف ---
    $pdf .= "trailer\n<</Size 6/Root 1 0 R>>\n";
    $pdf .= "startxref\n$xref_pos\n%%EOF\n";

    // --- نحفظ الملف ---
    return file_put_contents($save_path, $pdf) !== false;
}

// ============================================================
// MAIN — تشغيل الإعداد
// ============================================================

$pdf_dir = __DIR__ . '/assets/pdfs/';

// ننشئ مجلد PDFs إذا ما كانش موجود
if (!is_dir($pdf_dir)) {
    mkdir($pdf_dir, 0755, true);
}

// نجيب الكتب من قاعدة البيانات
$stmt = $pdo->query("SELECT id, title, author, pdf_file FROM books ORDER BY id");
$books = $stmt->fetchAll();

$results = [];

foreach ($books as $book) {
    $pdf_file = $book['pdf_file'] ?? ('book_' . str_pad($book['id'], 2, '0', STR_PAD_LEFT) . '.pdf');
    $save_path = $pdf_dir . $pdf_file;

    if (file_exists($save_path)) {
        $results[] = ['status' => 'skip', 'file' => $pdf_file, 'title' => $book['title']];
    } else {
        $ok = create_simple_pdf($book['title'], $book['author'], $save_path);
        $results[] = ['status' => $ok ? 'created' : 'error', 'file' => $pdf_file, 'title' => $book['title']];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعداد ملفات PDF</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; }
        h1   { color: #6c63ff; }
        .item { padding: 10px 16px; margin: 6px 0; border-radius: 8px; display: flex; justify-content: space-between; }
        .created { background: #d4edda; color: #155724; }
        .skip    { background: #fff3cd; color: #856404; }
        .error   { background: #f8d7da; color: #721c24; }
        .note { background: #e8f4f8; border: 1px solid #bee5eb; border-radius: 8px; padding: 16px; margin-top: 24px; }
        a { color: #6c63ff; font-weight: 700; }
    </style>
</head>
<body>
<h1>⚙️ إعداد ملفات PDF</h1>

<?php foreach ($results as $r): ?>
    <div class="item <?= $r['status'] ?>">
        <span>
            <?= $r['status'] === 'created' ? '✅' : ($r['status'] === 'skip' ? '⏭️' : '❌') ?>
            <?= htmlspecialchars($r['title']) ?>
        </span>
        <code><?= htmlspecialchars($r['file']) ?></code>
    </div>
<?php endforeach; ?>

<div class="note">
    <p><strong>📌 ملاحظة مهمة:</strong></p>
    <p>الملفات التجريبية جاهزة. لتضع كتباً حقيقية:</p>
    <ol>
        <li>ضع ملفات PDF الحقيقية في مجلد: <code>library/assets/pdfs/</code></li>
        <li>استخدم نفس الأسماء: <code>book_01.pdf</code>, <code>book_02.pdf</code>, ...</li>
        <li>الملف القديم سيُستبدل تلقائياً</li>
    </ol>
    <p>
        <a href="index.php">← العودة للموقع</a>
    </p>
</div>
</body>
</html>

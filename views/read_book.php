<?php
// =====================================================
// read_book.php — قراءة الكتاب
// الحماية: يتحقق من قاعدة البيانات قبل أي شيء
// =====================================================

session_start();
require_once '../core/db.php';

// ── الخطوة 1: يجب أن يكون مسجل الدخول ──────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // نوقف كل شيء هنا
}

// ── الخطوة 2: يجب أن يكون فيه ID كتاب في الـ URL ───
$book_id = $_GET['id'] ?? null;

if (!$book_id || !is_numeric($book_id)) {
    header("Location: catalogue.php");
    exit();
}

// ── الخطوة 3: نجلب بيانات الكتاب ───────────────────
$stmt = $pdo->prepare("SELECT id, title, author, content FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: catalogue.php");
    exit();
}

// ── الخطوة 4: التحقق من الصلاحية في قاعدة البيانات ─
// الشروط:
//   - user_id = المستخدم الحالي
//   - book_id = الكتاب المطلوب
//   - paid = 1 (أكمل الدفع)
//   - rent_until >= اليوم (الكراء لم ينته بعد)
$stmt = $pdo->prepare("
    SELECT id, rent_until
    FROM rentals
    WHERE user_id    = ?
      AND book_id    = ?
      AND paid       = 1
      AND rent_until >= CURDATE()
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id'], $book_id]);
$rental = $stmt->fetch();

// إذا لم يجد سجل rent صالح → نوقف كل شيء هنا
// لا نكمل تحميل الصفحة، لا نعرض أي محتوى
if (!$rental) {
    // نعرض رسالة الرفض ونوقف
    ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>غير مسموح - <?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <h1>📚 Library</h1>
    <div class="nav-links">
        <a href="catalogue.php">الكتالوج</a>
        <a href="user_dashboard.php">حسابي</a>
        <a href="../core/logout.php">خروج</a>
    </div>
</nav>

<div class="buy-container">

    <!-- معلومات الكتاب -->
    <div class="book-summary">
        <h2><?= htmlspecialchars($book['title']) ?></h2>
        <p>المؤلف: <strong><?= htmlspecialchars($book['author']) ?></strong></p>
    </div>

    <!-- رسالة الرفض -->
    <div style="
        background: #f8d7da;
        border: 2px solid #f5c6cb;
        border-radius: 12px;
        padding: 36px;
        text-align: center;
        margin: 24px 0;
    ">
        <p style="font-size: 48px; margin-bottom: 16px;">🔒</p>
        <p style="font-size: 22px; color: #721c24; font-weight: 700; margin-bottom: 12px;">
            يجب عليك كراء الكتاب أولاً.
        </p>
        <p style="color: #721c24; margin-bottom: 24px; font-size: 15px;">
            قراءة الكتب متاحة فقط بعد إتمام عملية الكراء والدفع.<br>
            عملية الشراء لا تعطي صلاحية القراءة الإلكترونية.
        </p>
        <a href="rent.php?id=<?= $book['id'] ?>" class="btn-primary">
            📖 كراء هذا الكتاب الآن
        </a>
    </div>

    <a href="catalogue.php" class="btn-back">⬅ العودة للكتالوج</a>

</div>

</body>
</html>
<?php
    exit(); // نوقف هنا — لا يكمل الكود أبداً
}

// =====================================================
// من وصل إلى هنا = مستخدم مسجل + لديه Rent مدفوع وسارٍ
// الآن فقط نعرض محتوى الكتاب
// =====================================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قراءة: <?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* منع نسخ المحتوى */
        .book-content {
            user-select: none;
            -webkit-user-select: none;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <h1>📚 Library</h1>
    <div class="nav-links">
        <a href="catalogue.php">الكتالوج</a>
        <a href="user_dashboard.php">حسابي</a>
        <a href="../core/logout.php">خروج</a>
    </div>
</nav>

<div class="buy-container">

    <!-- رأس الكتاب -->
    <div class="book-summary">
        <h2>📖 <?= htmlspecialchars($book['title']) ?></h2>
        <p>المؤلف: <strong><?= htmlspecialchars($book['author']) ?></strong></p>
        <p style="color: var(--success); font-weight: 600; margin-top: 8px;">
            ✅ كراؤك سارٍ حتى:
            <strong><?= date('d/m/Y', strtotime($rental['rent_until'])) ?></strong>
        </p>
    </div>

    <!-- محتوى الكتاب -->
    <div class="book-content" style="
        background: #fff;
        padding: 36px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-top: 20px;
        line-height: 2.2;
        font-size: 16px;
        white-space: pre-wrap;
        direction: rtl;
        text-align: right;
    ">
        <?= nl2br(htmlspecialchars($book['content'] ?? 'لا يوجد محتوى لهذا الكتاب بعد.')) ?>
    </div>

    <a href="catalogue.php" class="btn-back" style="margin-top: 24px; display: inline-block;">
        ⬅ العودة للكتالوج
    </a>

</div>

</body>
</html>

<?php
/*
 * ============================================================
 * الملف: buy.php
 * الوظيفة: فورم الشراء — يجمع البيانات ويحفظها في الـ Session
 * ============================================================
 *
 * كيف يشتغل هذا الملف؟
 *
 *  الزبون يضغط "اشترِ" في index.php
 *       ↓
 *  يفتح buy.php?id=3 مثلاً
 *       ↓
 *  يملأ الفورم (الاسم + الهاتف + المدينة)
 *       ↓
 *  يضغط "التالي"
 *       ↓
 *  نتحقق من البيانات
 *  إذا صحيحة → نحفظ في SESSION → نوجهوه لـ payment.php
 *  إذا فيه خطأ → نعرض الخطأ في نفس الصفحة
 *
 * SESSION = مخزن مؤقت لكل زائر — مثل "سلة التسوق"
 * يبقى حي طوال فتح المتصفح
 * ============================================================
 */

// --- دائماً ابدأ بـ session_start() قبل أي كود ---
// هذا يفعّل الـ SESSION لهذا الزائر
session_start();

// --- نتصل بقاعدة البيانات ---
require_once 'core/db.php';

// ============================================================
// خطوة 1: نجيب رقم الكتاب من الرابط
// ============================================================
// ?id=3 في الرابط يعني: $_GET['id'] = '3'
// ?? null = إذا ما كانش، نضع null
$book_id = $_GET['id'] ?? null;

// إذا ما جاش رقم كتاب، نرجعوه للرئيسية
if (!$book_id) {
    header("Location: index.php");
    exit(); // نوقف الصفحة هنا
}

// ============================================================
// خطوة 2: نجيب معلومات الكتاب من قاعدة البيانات
// ============================================================
// prepare = نحضر السؤال بشكل آمن (يمنع SQL Injection)
// ? = مكان للمتغير (سيُملأ في execute)
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(); // fetch = نجيب نتيجة واحدة فقط

// إذا الكتاب ما موجودش في قاعدة البيانات
if (!$book) {
    header("Location: index.php");
    exit();
}

// ============================================================
// خطوة 3: نهيئوا متغيرات الأخطاء والبيانات القديمة
// ============================================================
// $errors = قائمة فارغة -> ستُملأ إذا كان في أخطاء
$errors = [];

// $old_* = نحفظ ما كتبه المستخدم لنعرضه مجدداً إذا كان في خطأ
// مثلاً: كتب اسمه لكن نسي المدينة
// → نعرض اسمه مرة أخرى في الحقل (ما يكتبوه مرة ثانية)
$old_name  = '';
$old_phone = '';
$old_city  = '';

// ============================================================
// خطوة 4: هل المستخدم ضغط "التالي"؟
// ============================================================
// $_SERVER['REQUEST_METHOD'] = طريقة تحميل الصفحة
// 'GET' = فتح الرابط مباشرة
// 'POST' = إرسال فورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- نجيب البيانات من الفورم ---
    // trim() = نحذف المسافات الزائدة قبل وبعد النص
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $city      = trim($_POST['city']      ?? '');

    // نحفظها في متغيرات "القديمة" لنعرضها
    $old_name  = $full_name;
    $old_phone = $phone;
    $old_city  = $city;

    // --- التحقق من البيانات ---
    // empty() = هل الحقل فارغ؟

    if (empty($full_name)) {
        $errors[] = "⚠️ الاسم الكامل مطلوب";
    } elseif (strlen($full_name) < 3) {
        // strlen() = عدد الحروف في النص
        $errors[] = "⚠️ الاسم قصير جداً (3 حروف على الأقل)";
    }

    if (empty($phone)) {
        $errors[] = "⚠️ رقم الهاتف مطلوب";
    } elseif (!preg_match('/^[0-9+\s]{9,15}$/', $phone)) {
        // preg_match = يتحقق إذا النص يتطابق مع نمط معين
        // ^ = البداية, $ = النهاية, [0-9+\s] = أرقام أو + أو مسافة
        // {9,15} = بين 9 و 15 حرفاً
        $errors[] = "⚠️ رقم الهاتف غير صحيح (أرقام فقط، 9-15 خانة)";
    }

    if (empty($city)) {
        $errors[] = "⚠️ المدينة مطلوبة";
    }

    // --- إذا لا توجد أخطاء: نحفظ البيانات ونوجهه للدفع ---
    if (empty($errors)) {

        // SESSION = مخزن مؤقت — مثل علبة نحط فيها بيانات الزبون
        // $_SESSION['order_data'] = نحفظ كل بيانات الطلب في مكان واحد
        $_SESSION['order_data'] = [
            'book_id'    => $book['id'],
            'book_title' => $book['title'],
            'book_pdf'   => $book['pdf_file'],
            'price'      => $book['price_buy'],
            'order_type' => 'buy',  // نوع الطلب = شراء
            'full_name'  => $full_name,
            'phone'      => $phone,
            'city'       => $city,
            'email'      => null,
            'rent_days'  => null,
        ];

        // نوجهوه لصفحة الدفع
        // header() = يحول المتصفح لرابط آخر
        header("Location: payment.php");
        exit();
    }
}
// ============================================================
// خطوة 5: نعرض صفحة HTML
// ============================================================
// هذا الجزء يظهر دائماً:
// - لأول مرة (GET): يعرض الفورم فارغاً
// - بعد خطأ (POST): يعرض الفورم مع الأخطاء والبيانات القديمة
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛍️ شراء — <?= htmlspecialchars($book['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- ══════════════ NAVBAR ══════════════ -->
<nav class="navbar">
    <div class="nav-brand">📚 مكتبتي</div>
    <div class="nav-links">
        <a href="index.php">← الرئيسية</a>
    </div>
</nav>

<!-- ══════════════ الصفحة ══════════════ -->
<main class="form-page">
<div class="form-container">

    <!-- رأس الصفحة -->
    <div class="form-page-header">
        <div class="form-icon">🛍️</div>
        <h1>شراء الكتاب</h1>
        <p class="form-subtitle">بعد الشراء ستحصل على نسخة PDF دائمة 📥</p>
    </div>

    <!-- ══ ملخص الكتاب ══ -->
    <!-- نعرض معلومات الكتاب الذي يريد شراءه -->
    <div class="book-summary-card">
        <div class="summary-info">
            <h3><?= htmlspecialchars($book['title']) ?></h3>
            <p>✍️ <?= htmlspecialchars($book['author']) ?></p>
            <p class="summary-access">✅ وصول دائم + تحميل PDF مجاناً</p>
        </div>
        <div class="summary-price">
            <span class="price-tag buy-price"><?= number_format($book['price_buy'], 2) ?> درهم</span>
        </div>
    </div>

    <!-- ══ عرض الأخطاء ══ -->
    <!-- هذا القسم يظهر فقط إذا كان في أخطاء -->
    <?php if (!empty($errors)): ?>
        <div class="errors-box">
            <?php foreach ($errors as $error): ?>
                <!-- نمر على كل خطأ ونعرضه -->
                <p class="error-item"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ══ الفورم ══ -->
    <!--
        method="POST" = البيانات ترسل بشكل سري (لا تظهر في الرابط)
        action=""     = يرسل لنفس الصفحة (buy.php)
    -->
    <form method="POST" action="" class="order-form" id="buy-form">

        <!-- حقل الاسم الكامل -->
        <div class="form-group">
            <label for="full_name">👤 الاسم الكامل</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                placeholder="مثال: محمد أحمد الحسن"
                value="<?= htmlspecialchars($old_name) ?>"
                required
                autocomplete="name"
            >
            <!--
                value="..." = نعيد ما كتبه المستخدم إذا كان في خطأ
                required   = الحقل إجباري — المتصفح لا يرسل بدونه
            -->
        </div>

        <!-- حقل رقم الهاتف -->
        <div class="form-group">
            <label for="phone">📱 رقم الهاتف</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="مثال: 0612345678"
                value="<?= htmlspecialchars($old_phone) ?>"
                required
                autocomplete="tel"
            >
        </div>

        <!-- حقل المدينة -->
        <div class="form-group">
            <label for="city">🏙️ المدينة</label>
            <input
                type="text"
                id="city"
                name="city"
                placeholder="مثال: الرباط، الدار البيضاء..."
                value="<?= htmlspecialchars($old_city) ?>"
                required
                autocomplete="address-level2"
            >
        </div>

        <!-- ملخص السعر -->
        <div class="price-summary">
            <span>💰 المبلغ الإجمالي:</span>
            <strong class="total-amount"><?= number_format($book['price_buy'], 2) ?> درهم</strong>
        </div>

        <!-- زر الإرسال -->
        <button type="submit" class="btn btn-confirm" id="btn-submit-buy">
            التالي — اختيار طريقة الدفع 💳
        </button>

    </form>

    <!-- رابط الرجوع -->
    <a href="index.php" class="back-link">← العودة للرئيسية</a>

</div>
</main>

<!-- ══════════════ FOOTER ══════════════ -->
<footer class="footer">
    <p>📚 مكتبتي — جميع الحقوق محفوظة <?= date('Y') ?></p>
</footer>

</body>
</html>

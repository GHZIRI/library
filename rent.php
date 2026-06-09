<?php
/*
 * ============================================================
 * الملف: rent.php
 * الوظيفة: فورم الكراء — يجمع بيانات الكراء
 * ============================================================
 *
 * كيف يشتغل؟
 * 1. نجيب رقم الكتاب من الرابط
 * 2. المستخدم يملأ الفورم:
 *    - الاسم الكامل
 *    - البريد الإلكتروني
 *    - رقم الهاتف
 *    - مدة الكراء (بالأيام)
 * 3. نحسب السعر الإجمالي تلقائياً
 * 4. إذا كل شيء صحيح → نوجهوه لصفحة الدفع
 * ============================================================
 */

require_once 'core/db.php';

// --- نجيب رقم الكتاب ---
$book_id = $_GET['id'] ?? null;

if (!$book_id) {
    header("Location: index.php");
    exit();
}

// --- نجيب معلومات الكتاب ---
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: index.php");
    exit();
}

// --- متغيرات للأخطاء والبيانات القديمة ---
$errors    = [];
$old_name  = '';
$old_email = '';
$old_phone = '';
$old_days  = 7; // القيمة الافتراضية 7 أيام

// ============================================================
// إذا المستخدم ضغط "تأكيد الكراء"
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- نجيبوا البيانات من الفورم ---
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $days      = (int)($_POST['days']     ?? 0); // (int) = نحوله لرقم صحيح

    // --- نحفظوها لنعرضوها إذا كان في خطأ ---
    $old_name  = $full_name;
    $old_email = $email;
    $old_phone = $phone;
    $old_days  = $days;

    // --- التحقق من البيانات ---

    if (empty($full_name)) {
        $errors[] = "⚠️ الاسم الكامل مطلوب";
    }

    if (empty($email)) {
        $errors[] = "⚠️ البريد الإلكتروني مطلوب";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var = يتحقق إذا البريد في الشكل الصحيح
        $errors[] = "⚠️ البريد الإلكتروني غير صحيح";
    }

    if (empty($phone)) {
        $errors[] = "⚠️ رقم الهاتف مطلوب";
    } elseif (strlen($phone) < 9) {
        $errors[] = "⚠️ رقم الهاتف قصير جداً";
    }

    if ($days < 1) {
        $errors[] = "⚠️ مدة الكراء لازم تكون يوم واحد على الأقل";
    } elseif ($days > 90) {
        $errors[] = "⚠️ مدة الكراء لا يمكن أن تتجاوز 90 يوم";
    }

    // --- إذا ما كانش أخطاء، نوجهوه لصفحة الدفع ---
    if (empty($errors)) {

        // نحسب السعر الإجمالي
        $total = $book['price_rent'] * $days;

        $params = http_build_query([
            'type'      => 'rent',
            'book_id'   => $book['id'],
            'book_name' => $book['title'],
            'amount'    => $total,
            'full_name' => $full_name,
            'email'     => $email,
            'phone'     => $phone,
            'days'      => $days,
        ]);

        header("Location: payment.php?" . $params);
        exit();
    }
}

// --- نحسب السعر بناءً على الأيام المختارة (لعرضه في الفورم) ---
$preview_total = $book['price_rent'] * $old_days;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📅 كراء — <?= htmlspecialchars($book['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- الشريط العلوي -->
<nav class="navbar">
    <div class="nav-brand">📚 مكتبتي</div>
    <div class="nav-links">
        <a href="index.php">← الرئيسية</a>
    </div>
</nav>

<!-- ============================================================
     صفحة الكراء
     ============================================================ -->
<main class="form-page">

    <div class="form-container">

        <!-- عنوان الصفحة -->
        <div class="form-page-header">
            <div class="form-icon">📅</div>
            <h1>استئجار الكتاب</h1>
        </div>

        <!-- ملخص الكتاب -->
        <div class="book-summary-card">
            <div class="summary-info">
                <h3><?= htmlspecialchars($book['title']) ?></h3>
                <p>✍️ <?= htmlspecialchars($book['author']) ?></p>
            </div>
            <div class="summary-price">
                <span class="price-tag rent-price"><?= $book['price_rent'] ?> درهم/يوم</span>
            </div>
        </div>

        <!-- عرض الأخطاء -->
        <?php if (!empty($errors)): ?>
            <div class="errors-box">
                <?php foreach ($errors as $error): ?>
                    <p class="error-item"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============================================================
             فورم الكراء
             ============================================================ -->
        <form method="POST" action="" class="order-form">

            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

            <!-- --- الاسم الكامل --- -->
            <div class="form-group">
                <label for="full_name">👤 الاسم الكامل</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="مثال: فاطمة الزهراء"
                    value="<?= htmlspecialchars($old_name) ?>"
                    required
                >
            </div>

            <!-- --- البريد الإلكتروني --- -->
            <div class="form-group">
                <label for="email">📧 البريد الإلكتروني</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="مثال: example@gmail.com"
                    value="<?= htmlspecialchars($old_email) ?>"
                    required
                >
                <!-- type="email" = المتصفح يتحقق تلقائياً من شكل البريد -->
            </div>

            <!-- --- رقم الهاتف --- -->
            <div class="form-group">
                <label for="phone">📱 رقم الهاتف</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="مثال: 0612345678"
                    value="<?= htmlspecialchars($old_phone) ?>"
                    required
                >
            </div>

            <!-- --- مدة الكراء --- -->
            <div class="form-group">
                <label for="days">⏳ مدة الكراء (بالأيام)</label>
                <input
                    type="number"
                    id="days"
                    name="days"
                    min="1"
                    max="90"
                    value="<?= (int)$old_days ?>"
                    required
                    oninput="updateTotal(this.value)"
                >
                <!--
                    oninput="updateTotal()" = في كل مرة يغير المستخدم العدد،
                    نحسب السعر تلقائياً (JavaScript صغير أسفل الصفحة)
                -->
            </div>

            <!-- حاسبة السعر التلقائية -->
            <div class="price-summary rent-calculator">
                <div class="calc-row">
                    <span>💵 السعر اليومي:</span>
                    <strong><?= $book['price_rent'] ?> درهم</strong>
                </div>
                <div class="calc-row">
                    <span>📅 عدد الأيام:</span>
                    <strong id="display-days"><?= $old_days ?></strong>
                </div>
                <div class="calc-row total-row">
                    <span>💰 المجموع:</span>
                    <strong id="display-total" class="total-amount">
                        <?= $preview_total ?> درهم
                    </strong>
                </div>
            </div>

            <!-- زر التأكيد -->
            <button type="submit" class="btn btn-confirm" id="btn-confirm-rent">
                ✅ تأكيد الكراء والمتابعة للدفع
            </button>

        </form>

        <!-- رابط الرجوع -->
        <a href="index.php" class="back-link">← العودة للرئيسية</a>

    </div>
</main>

<footer class="footer">
    <p>📚 مكتبتي — جميع الحقوق محفوظة <?= date('Y') ?></p>
</footer>

<!-- ============================================================
     JavaScript بسيط — يحسب السعر تلقائياً
     ============================================================
     هذا كود JavaScript صغير:
     - يسمع للتغيير في حقل "عدد الأيام"
     - يضرب العدد × السعر اليومي
     - يعرض النتيجة في الصفحة بدون تحديث
-->
<script>
    // السعر اليومي — نجيبوه من PHP
    var pricePerDay = <?= (float)$book['price_rent'] ?>;

    // الدالة اللي تحسب السعر
    function updateTotal(days) {
        // نتحقق إن العدد رقم موجب
        var numDays = parseInt(days);
        if (isNaN(numDays) || numDays < 1) {
            numDays = 0;
        }

        // نحسب المجموع
        var total = numDays * pricePerDay;

        // نحدث النص في الصفحة
        document.getElementById('display-days').textContent = numDays;
        document.getElementById('display-total').textContent = total.toFixed(2) + ' درهم';
    }
</script>

</body>
</html>

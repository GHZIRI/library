<?php
/*
 * ============================================================
 * الملف: payment.php
 * الوظيفة: واجهة الدفع — تعرض ملخص الطلب وخيارات الدفع
 * ============================================================
 *
 * هذه الصفحة تستقبل البيانات من buy.php أو rent.php
 * وتعرض:
 *   1. ملخص الطلب (الكتاب، السعر، بيانات الزبون)
 *   2. خيارات الدفع (بطاقة / تحويل / عند الاستلام)
 *   3. زر "إتمام الدفع" يظهر رسالة نجاح
 *
 * ملاحظة مهمة للمبتدئ:
 *   - هذا الدفع وهمي — لا يوجد دفع حقيقي
 *   - في الواقع، نحتاج خدمة مثل PayPal أو Stripe
 * ============================================================
 */

// --- نجيبوا البيانات من الرابط (GET) ---
// هذه البيانات أتت من buy.php أو rent.php
$type      = $_GET['type']      ?? '';        // buy أو rent
$book_name = $_GET['book_name'] ?? 'كتاب';
$amount    = (float)($_GET['amount']    ?? 0);
$full_name = $_GET['full_name'] ?? '';
$phone     = $_GET['phone']     ?? '';

// بيانات خاصة بالشراء
$city      = $_GET['city']      ?? '';

// بيانات خاصة بالكراء
$email     = $_GET['email']     ?? '';
$days      = (int)($_GET['days']      ?? 0);

// --- تحقق بسيط: إذا ما كانش المبلغ، ارجع للرئيسية ---
if ($amount <= 0 || empty($type)) {
    header("Location: index.php");
    exit();
}

// --- نحضر النص حسب نوع الطلب ---
if ($type === 'buy') {
    $order_type_text = "🛍️ شراء كتاب";
    $order_icon      = "🛍️";
} else {
    $order_type_text = "📅 كراء كتاب";
    $order_icon      = "📅";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💳 صفحة الدفع — مكتبتي</title>
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
     صفحة الدفع
     ============================================================ -->
<main class="payment-page">

    <!-- شريط التقدم — يوضح للمستخدم في أي خطوة هو -->
    <div class="progress-bar">
        <div class="step done">
            <div class="step-circle">✅</div>
            <span>البيانات</span>
        </div>
        <div class="step-line done"></div>
        <div class="step active">
            <div class="step-circle">💳</div>
            <span>الدفع</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">🎉</div>
            <span>تأكيد</span>
        </div>
    </div>

    <div class="payment-layout">

        <!-- ============================================================
             يسار — ملخص الطلب
             ============================================================ -->
        <div class="order-summary">
            <h2>📋 ملخص الطلب</h2>

            <!-- نوع الطلب -->
            <div class="summary-badge"><?= $order_type_text ?></div>

            <!-- اسم الكتاب -->
            <div class="summary-row">
                <span>📖 الكتاب:</span>
                <strong><?= htmlspecialchars($book_name) ?></strong>
            </div>

            <!-- بيانات الزبون -->
            <div class="summary-row">
                <span>👤 الاسم:</span>
                <strong><?= htmlspecialchars($full_name) ?></strong>
            </div>

            <div class="summary-row">
                <span>📱 الهاتف:</span>
                <strong><?= htmlspecialchars($phone) ?></strong>
            </div>

            <!-- بيانات حسب النوع -->
            <?php if ($type === 'buy'): ?>
                <div class="summary-row">
                    <span>🏙️ المدينة:</span>
                    <strong><?= htmlspecialchars($city) ?></strong>
                </div>
            <?php else: ?>
                <div class="summary-row">
                    <span>📧 البريد:</span>
                    <strong><?= htmlspecialchars($email) ?></strong>
                </div>
                <div class="summary-row">
                    <span>⏳ المدة:</span>
                    <strong><?= $days ?> يوم</strong>
                </div>
            <?php endif; ?>

            <!-- المبلغ الإجمالي -->
            <div class="summary-total">
                <span>💰 المبلغ الإجمالي:</span>
                <strong class="total-big"><?= number_format($amount, 2) ?> درهم</strong>
            </div>
        </div>

        <!-- ============================================================
             يمين — خيارات الدفع
             ============================================================ -->
        <div class="payment-options">
            <h2>💳 اختر طريقة الدفع</h2>

            <!-- خيارات الدفع — نعرضها كأزرار يختار منها المستخدم -->
            <div class="payment-methods">

                <!-- طريقة 1: بطاقة بنكية -->
                <label class="payment-method active" id="method-card" onclick="selectMethod('card')">
                    <input type="radio" name="payment_method" value="card" checked hidden>
                    <div class="method-icon">💳</div>
                    <div class="method-info">
                        <strong>بطاقة بنكية</strong>
                        <small>Visa / Mastercard</small>
                    </div>
                    <div class="method-check">✓</div>
                </label>

                <!-- طريقة 2: تحويل بنكي -->
                <label class="payment-method" id="method-transfer" onclick="selectMethod('transfer')">
                    <input type="radio" name="payment_method" value="transfer" hidden>
                    <div class="method-icon">🏦</div>
                    <div class="method-info">
                        <strong>تحويل بنكي</strong>
                        <small>تحويل مباشر للحساب</small>
                    </div>
                    <div class="method-check">✓</div>
                </label>

                <!-- طريقة 3: الدفع عند الاستلام -->
                <label class="payment-method" id="method-cash" onclick="selectMethod('cash')">
                    <input type="radio" name="payment_method" value="cash" hidden>
                    <div class="method-icon">💵</div>
                    <div class="method-info">
                        <strong>الدفع عند الاستلام</strong>
                        <small>ادفع حين يصل الكتاب</small>
                    </div>
                    <div class="method-check">✓</div>
                </label>

            </div>

            <!-- ============================================================
                 حقول بطاقة الائتمان (تظهر فقط عند اختيار "بطاقة بنكية")
                 ============================================================ -->
            <div class="card-fields" id="card-fields">

                <div class="form-group">
                    <label>🔢 رقم البطاقة</label>
                    <input
                        type="text"
                        id="card-number"
                        placeholder="1234  5678  9012  3456"
                        maxlength="19"
                        oninput="formatCardNumber(this)"
                    >
                    <!--
                        oninput="formatCardNumber()" = يضيف مسافة كل 4 أرقام
                        تلقائياً (JavaScript أسفل الصفحة)
                    -->
                </div>

                <div class="card-row-two">
                    <div class="form-group">
                        <label>📅 تاريخ الانتهاء</label>
                        <input type="text" id="card-expiry" placeholder="MM/YY" maxlength="5" oninput="formatExpiry(this)">
                    </div>

                    <div class="form-group">
                        <label>🔒 CVV</label>
                        <input type="password" id="card-cvv" placeholder="123" maxlength="3">
                    </div>
                </div>

                <div class="form-group">
                    <label>👤 اسم حامل البطاقة</label>
                    <input type="text" id="card-name" placeholder="الاسم كما هو مكتوب على البطاقة">
                </div>

                <!-- شعارات البطاقات والأمان -->
                <div class="security-badges">
                    <span class="badge">🔐 SSL آمن</span>
                    <span class="badge">💳 Visa</span>
                    <span class="badge">💳 Mastercard</span>
                </div>

            </div>

            <!-- رسالة تحويل بنكي -->
            <div class="transfer-info" id="transfer-info" style="display:none;">
                <div class="info-box">
                    <p>🏦 <strong>معلومات الحساب البنكي:</strong></p>
                    <p>البنك: بنك المغرب</p>
                    <p>رقم الحساب: MA64 0000 1234 5678 9012 3456</p>
                    <p>المبلغ: <strong><?= number_format($amount, 2) ?> درهم</strong></p>
                    <p class="note">⚠️ يرجى كتابة اسمك في خانة الملاحظات</p>
                </div>
            </div>

            <!-- رسالة الدفع عند الاستلام -->
            <div class="cash-info" id="cash-info" style="display:none;">
                <div class="info-box">
                    <p>💵 <strong>الدفع عند الاستلام:</strong></p>
                    <p>✅ سيتم التواصل معك على الرقم <strong><?= htmlspecialchars($phone) ?></strong></p>
                    <p>⏰ في غضون 24 ساعة من تأكيد الطلب</p>
                </div>
            </div>

            <!-- ============================================================
                 زر إتمام الدفع
                 ============================================================ -->
            <button
                class="btn btn-pay"
                id="btn-pay"
                onclick="completePay()"
            >
                🔒 إتمام الدفع — <?= number_format($amount, 2) ?> درهم
            </button>

        </div>
    </div>

    <!-- ============================================================
         رسالة النجاح — تظهر بعد الضغط على "إتمام الدفع"
         (مخفية في البداية)
         ============================================================ -->
    <div class="success-overlay" id="success-overlay" style="display:none;">
        <div class="success-box">
            <div class="success-icon">🎉</div>
            <h2>تم الطلب بنجاح!</h2>
            <p>شكراً لك يا <strong><?= htmlspecialchars($full_name) ?></strong></p>
            <p>تم تأكيد <?= $type === 'buy' ? 'شراء' : 'كراء' ?> كتاب
                <strong>"<?= htmlspecialchars($book_name) ?>"</strong>
            </p>
            <p class="success-amount">المبلغ المدفوع: <strong><?= number_format($amount, 2) ?> درهم</strong></p>
            <p class="success-note">📱 سيتم التواصل معك على: <strong><?= htmlspecialchars($phone) ?></strong></p>

            <div class="success-actions">
                <a href="index.php" class="btn btn-confirm">🏠 العودة للرئيسية</a>
            </div>
        </div>
    </div>

</main>

<footer class="footer">
    <p>📚 مكتبتي — جميع الحقوق محفوظة <?= date('Y') ?></p>
</footer>

<!-- ============================================================
     JavaScript — يتحكم في صفحة الدفع
     ============================================================
     3 وظائف بسيطة:
     1. selectMethod()   — يغير طريقة الدفع المختارة
     2. formatCardNumber() — يضيف مسافة كل 4 أرقام
     3. completePay()    — يعرض رسالة النجاح
-->
<script>

    // --- 1. اختيار طريقة الدفع ---
    function selectMethod(method) {
        // نزيل class "active" من جميع الطرق
        document.querySelectorAll('.payment-method').forEach(function(el) {
            el.classList.remove('active');
        });

        // نضيف "active" للطريقة المختارة
        document.getElementById('method-' + method).classList.add('active');

        // نخفي جميع الواجهات
        document.getElementById('card-fields').style.display   = 'none';
        document.getElementById('transfer-info').style.display = 'none';
        document.getElementById('cash-info').style.display     = 'none';

        // نعرض الواجهة المناسبة
        if (method === 'card') {
            document.getElementById('card-fields').style.display = 'block';
        } else if (method === 'transfer') {
            document.getElementById('transfer-info').style.display = 'block';
        } else if (method === 'cash') {
            document.getElementById('cash-info').style.display = 'block';
        }
    }

    // --- 2. تنسيق رقم البطاقة (مسافة كل 4 أرقام) ---
    function formatCardNumber(input) {
        // نحذف كل شيء ما عدا الأرقام
        var value = input.value.replace(/\D/g, '');

        // نضيف مسافة كل 4 أرقام
        var formatted = value.match(/.{1,4}/g);
        input.value = formatted ? formatted.join('  ') : '';
    }

    // --- تنسيق تاريخ الانتهاء MM/YY ---
    function formatExpiry(input) {
        var value = input.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        input.value = value;
    }

    // --- 3. إتمام الدفع — يعرض رسالة النجاح ---
    function completePay() {
        // نغير نص الزر ونجعله غير قابل للضغط
        var btn = document.getElementById('btn-pay');
        btn.textContent = '⌛ جارِ المعالجة...';
        btn.disabled = true;
        btn.style.opacity = '0.7';

        // بعد ثانيتين، نعرض رسالة النجاح
        // setTimeout = ننتظر X ميلي ثانية ثم ننفذ الكود
        setTimeout(function() {
            document.getElementById('success-overlay').style.display = 'flex';
            // نمنع التمرير خلف الـ overlay
            document.body.style.overflow = 'hidden';
        }, 2000); // 2000 = ثانيتان
    }

</script>

</body>
</html>

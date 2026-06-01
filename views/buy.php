<?php
/**
 * صفحة الشراء
 * 
 * نموذج لشراء الكتاب بدون الحاجة لتسجيل دخول
 */

require_once '../core/functions.php';

// التحقق من وجود معرف الكتاب
if (empty($_GET['book_id'])) {
    redirect('catalogue.php');
}

$book_id = sanitize($_GET['book_id']);
$book = getBookById($book_id);

// إذا لم نجد الكتاب أو غير متاح للشراء
if (!$book || !$book['available_buy']) {
    redirect('catalogue.php');
}

// معالجة الشراء (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'خطأ في الأمان. حاول مرة أخرى.');
        redirect("buy.php?book_id={$book_id}");
    }

    // التحقق من البيانات
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);

    if (empty($name) || empty($phone) || empty($city) || $quantity < 1) {
        setFlash('error', 'يرجى ملء جميع الحقول بشكل صحيح.');
        redirect("buy.php?book_id={$book_id}");
    }

    // إنشاء الطلب
    $total_price = $book['price_buy'] * $quantity;
    $order_data = [
        'user_id' => getCurrentUserId(),
        'book_id' => $book_id,
        'name' => $name,
        'phone' => $phone,
        'city' => $city,
        'quantity' => $quantity,
        'total_price' => $total_price
    ];

    if (createBuyOrder($order_data)) {
        setFlash('success', '✅ تم استقبال طلب الشراء! سنتواصل معك قريباً.');
        redirect('order_confirmation.php');
    } else {
        setFlash('error', 'حدث خطأ. حاول مرة أخرى لاحقاً.');
        redirect("buy.php?book_id={$book_id}");
    }
}

$success = getFlash('success');
$error = getFlash('error');
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شراء - <?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 مكتبة</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">الرئيسية</a></li>
                <li><a href="../admin/login.php">دخول الأدمين</a></li>
            </ul>
        </div>
    </nav>

    <!-- محتوى الصفحة -->
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 40px 0;">
            <!-- تفاصيل الكتاب -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px;">📖 تفاصيل الكتاب</h2>

                <!-- صورة الكتاب -->
                <div style="width: 100%; height: 300px; background-color: var(--light); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 80px; margin-bottom: 20px;">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                    <?php else: ?>
                        📖
                    <?php endif; ?>
                </div>

                <!-- معلومات الكتاب -->
                <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p style="color: var(--gray); margin-bottom: 15px;">✍️ <?php echo htmlspecialchars($book['author']); ?></p>
                <p style="margin-bottom: 15px;"><strong>النوع:</strong> <?php echo htmlspecialchars($book['type_name']); ?></p>
                <p style="margin-bottom: 15px;"><strong>السعر:</strong> <span style="color: var(--secondary); font-size: 20px; font-weight: 700;"><?php echo formatPrice($book['price_buy']); ?></span></p>
                <?php if (!empty($book['description'])): ?>
                    <p style="color: var(--gray); line-height: 1.8;"><?php echo htmlspecialchars($book['description']); ?></p>
                <?php endif; ?>
            </div>

            <!-- نموذج الشراء -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: fit-content;">
                <h2 style="margin-bottom: 20px;">🛒 بيانات الشراء</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- رمز الحماية -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                    <!-- الاسم -->
                    <div class="form-group">
                        <label>الاسم الكامل *</label>
                        <input type="text" name="name" placeholder="أدخل اسمك" required maxlength="100">
                    </div>

                    <!-- رقم الهاتف -->
                    <div class="form-group">
                        <label>رقم الهاتف *</label>
                        <input type="tel" name="phone" placeholder="مثال: 0612345678" required maxlength="20">
                    </div>

                    <!-- المدينة -->
                    <div class="form-group">
                        <label>المدينة *</label>
                        <input type="text" name="city" placeholder="أدخل المدينة" required maxlength="100">
                    </div>

                    <!-- الكمية -->
                    <div class="form-group">
                        <label>الكمية *</label>
                        <input type="number" name="quantity" value="1" min="1" required>
                    </div>

                    <!-- ملخص السعر -->
                    <div style="background-color: var(--light); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <p style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>سعر الكتاب:</span>
                            <span><?php echo formatPrice($book['price_buy']); ?></span>
                        </p>
                        <hr style="border: none; border-top: 1px solid var(--border); margin-bottom: 10px;">
                        <p style="display: flex; justify-content: space-between; font-weight: 700; font-size: 18px;">
                            <span>الإجمالي:</span>
                            <span style="color: var(--secondary);"><?php echo formatPrice($book['price_buy']); ?></span>
                        </p>
                    </div>

                    <!-- الأزرار -->
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success" style="flex: 1;">✅ تأكيد الشراء</button>
                        <a href="catalogue.php" class="btn btn-secondary" style="flex: 1;">❌ إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- التذييل -->
    <footer class="footer">
        <p>&copy; 2026 مكتبة. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>

<?php
/**
 * صفحة إنشاء حساب جديد
 * 
 * تسجيل مستخدم جديد
 */

require_once '../core/functions.php';

// إذا كان المستخدم مسجل دخول بالفعل
if (isLoggedIn()) {
    redirect('catalogue.php');
}

// معالجة إنشاء حساب (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'خطأ في الأمان. حاول مرة أخرى.');
        redirect('register.php');
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // التحقق من البيانات
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        setFlash('error', 'يرجى ملء جميع الحقول.');
        redirect('register.php');
    }

    if (strlen($password) < 6) {
        setFlash('error', 'كلمة السر يجب أن تكون 6 أحرف على الأقل.');
        redirect('register.php');
    }

    if ($password !== $confirm_password) {
        setFlash('error', 'كلمات السر غير متطابقة.');
        redirect('register.php');
    }

    // التحقق من عدم وجود البريد الإلكتروني
    if (getUserByEmail($email)) {
        setFlash('error', 'هذا البريد الإلكتروني موجود بالفعل.');
        redirect('register.php');
    }

    // إنشاء الحساب
    $user_data = [
        'name' => $name,
        'email' => $email,
        'password' => $password
    ];

    if (createUser($user_data)) {
        setFlash('success', '✅ تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.');
        redirect('login.php');
    } else {
        setFlash('error', 'حدث خطأ. حاول مرة أخرى لاحقاً.');
        redirect('register.php');
    }
}

$error = getFlash('error');
$success = getFlash('success');
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 مكتبة</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">الرئيسية</a></li>
                <li><a href="login.php">دخول</a></li>
            </ul>
        </div>
    </nav>

    <!-- صندوق إنشاء الحساب -->
    <div class="form-box">
        <h1>📝 إنشاء حساب جديد</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- رمز الحماية -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <!-- الاسم -->
            <div class="form-group">
                <label>الاسم الكامل *</label>
                <input type="text" name="name" placeholder="أدخل اسمك" required maxlength="100">
            </div>

            <!-- البريد الإلكتروني -->
            <div class="form-group">
                <label>البريد الإلكتروني *</label>
                <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>
            </div>

            <!-- كلمة السر -->
            <div class="form-group">
                <label>كلمة السر *</label>
                <input type="password" name="password" placeholder="6 أحرف على الأقل" required minlength="6">
            </div>

            <!-- تأكيد كلمة السر -->
            <div class="form-group">
                <label>تأكيد كلمة السر *</label>
                <input type="password" name="confirm_password" placeholder="أعد إدخال كلمة السر" required minlength="6">
            </div>

            <!-- زر الإنشاء -->
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">✅ إنشاء الحساب</button>

            <!-- رابط الدخول -->
            <p style="text-align: center; margin-top: 20px; color: var(--gray);">
                هل لديك حساب بالفعل؟ <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">دخول الآن</a>
            </p>
        </form>
    </div>

    <!-- التذييل -->
    <footer class="footer">
        <p>&copy; 2026 مكتبة. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>

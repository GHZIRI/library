<?php
/**
 * صفحة دخول الأدمين
 * 
 * تسجيل دخول للأدمين فقط
 */

require_once '../core/functions.php';

// إذا كان المستخدم أدمين بالفعل
if (isLoggedIn() && isAdmin()) {
    redirect('dashboard.php');
}

// معالجة تسجيل الدخول (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'خطأ في الأمان. حاول مرة أخرى.');
        redirect('login.php');
    }

    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'يرجى ملء جميع الحقول.');
        redirect('login.php');
    }

    // البحث عن المستخدم
    $user = getUserByEmail($email);

    if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
        // تسجيل دخول الأدمين
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = 'admin';
        
        setFlash('success', '✅ تم تسجيل الدخول بنجاح.');
        redirect('dashboard.php');
    } else {
        setFlash('error', '❌ البريد الإلكتروني أو كلمة السر غير صحيحة أو غير مصرح.');
        redirect('login.php');
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
    <title>دخول الأدمين</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <a href="../views/catalogue.php" class="navbar-brand">📚 مكتبة</a>
            <ul class="navbar-links">
                <li><a href="../views/catalogue.php">الرئيسية</a></li>
            </ul>
        </div>
    </nav>

    <!-- صندوق دخول الأدمين -->
    <div class="form-box">
        <h1>🔐 دخول الأدمين</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- رمز الحماية -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <!-- البريد الإلكتروني -->
            <div class="form-group">
                <label>البريد الإلكتروني *</label>
                <input type="email" name="email" placeholder="البريد الإلكتروني للأدمين" required>
            </div>

            <!-- كلمة السر -->
            <div class="form-group">
                <label>كلمة السر *</label>
                <input type="password" name="password" placeholder="أدخل كلمة السر" required>
            </div>

            <!-- زر الدخول -->
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">🔓 دخول</button>

            <!-- رابط العودة -->
            <p style="text-align: center; margin-top: 20px; color: var(--gray);">
                <a href="../views/catalogue.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">← العودة للرئيسية</a>
            </p>
        </form>
    </div>

    <!-- التذييل -->
    <footer class="footer">
        <p>&copy; 2026 مكتبة. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>

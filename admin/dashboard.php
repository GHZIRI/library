<?php
/**
 * داشبورد الأدمين
 * 
 * إدارة الكتب (إضافة، حذف، تعديل)
 */

require_once '../core/functions.php';

// فرض تسجيل دخول الأدمين
requireAdmin();

// معالجة إضافة كتاب (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // التحقق من CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'خطأ في الأمان. حاول مرة أخرى.');
        redirect('dashboard.php');
    }

    if ($_POST['action'] === 'add') {
        $title = sanitize($_POST['title'] ?? '');
        $author = sanitize($_POST['author'] ?? '');
        $type_id = intval($_POST['type_id'] ?? 0);
        $price_buy = floatval($_POST['price_buy'] ?? 0);
        $price_rental = floatval($_POST['price_rental'] ?? 0);

        if (empty($title) || empty($author) || $type_id <= 0 || $price_buy <= 0 || $price_rental <= 0) {
            setFlash('error', 'يرجى ملء جميع الحقول بشكل صحيح.');
            redirect('dashboard.php');
        }

        $book_data = [
            'title' => $title,
            'author' => $author,
            'type_id' => $type_id,
            'price_buy' => $price_buy,
            'price_rental' => $price_rental,
            'available_buy' => 1,
            'available_rental' => 1
        ];

        if (addBook($book_data)) {
            setFlash('success', '✅ تم إضافة الكتاب بنجاح.');
        } else {
            setFlash('error', 'حدث خطأ في إضافة الكتاب.');
        }
        redirect('dashboard.php');
    } elseif ($_POST['action'] === 'delete') {
        $book_id = intval($_POST['book_id'] ?? 0);

        if ($book_id <= 0) {
            setFlash('error', 'معرف الكتاب غير صحيح.');
            redirect('dashboard.php');
        }

        if (deleteBook($book_id)) {
            setFlash('success', '✅ تم حذف الكتاب بنجاح.');
        } else {
            setFlash('error', 'حدث خطأ في حذف الكتاب.');
        }
        redirect('dashboard.php');
    }
}

$books = getAllBooks('', 0);
$types = getAllTypes();
$success = getFlash('success');
$error = getFlash('error');
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد الأدمين</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar">
        <div class="container">
            <a href="../views/catalogue.php" class="navbar-brand">📚 مكتبة</a>
            <ul class="navbar-links">
                <li><a href="dashboard.php">📊 الداشبورد</a></li>
                <li><a href="../views/catalogue.php">الكتب</a></li>
                <li><a href="../core/logout.php">تسجيل الخروج</a></li>
            </ul>
        </div>
    </nav>

    <!-- محتوى الصفحة -->
    <div class="container">
        <h1 style="margin: 40px 0;">⚙️ لوحة التحكم</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- قسم إضافة كتاب -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
            <!-- نموذج الإضافة -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px;">➕ إضافة كتاب جديد</h2>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label>عنوان الكتاب *</label>
                        <input type="text" name="title" placeholder="أدخل عنوان الكتاب" required maxlength="200">
                    </div>

                    <div class="form-group">
                        <label>المؤلف *</label>
                        <input type="text" name="author" placeholder="أدخل اسم المؤلف" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label>النوع *</label>
                        <select name="type_id" required>
                            <option value="">اختر نوع الكتاب</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>سعر الشراء *</label>
                        <input type="number" name="price_buy" placeholder="مثال: 50" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>سعر الكراء (اليوم) *</label>
                        <input type="number" name="price_rental" placeholder="مثال: 5" step="0.01" required>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%;">✅ إضافة الكتاب</button>
                </form>
            </div>

            <!-- الإحصائيات -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px;">📊 الإحصائيات</h2>

                <div style="display: grid; gap: 15px;">
                    <div style="background-color: var(--light); padding: 15px; border-radius: 5px;">
                        <p style="color: var(--gray); margin-bottom: 5px;">📚 إجمالي الكتب</p>
                        <p style="font-size: 28px; font-weight: 700; color: var(--primary);"><?php echo count($books); ?></p>
                    </div>

                    <div style="background-color: var(--light); padding: 15px; border-radius: 5px;">
                        <p style="color: var(--gray); margin-bottom: 5px;">🏷️ عدد الأنواع</p>
                        <p style="font-size: 28px; font-weight: 700; color: var(--secondary);"><?php echo count($types); ?></p>
                    </div>

                    <div style="background-color: var(--light); padding: 15px; border-radius: 5px;">
                        <p style="color: var(--gray); margin-bottom: 5px;">📍 الحالة</p>
                        <p style="font-size: 18px; color: var(--success);">🟢 النظام يعمل بشكل صحيح</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- قائمة الكتب -->
        <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 20px;">📚 قائمة الكتب</h2>

            <?php if (count($books) > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: var(--light); border-bottom: 2px solid var(--border);">
                            <th style="padding: 12px; text-align: right;">العنوان</th>
                            <th style="padding: 12px; text-align: right;">المؤلف</th>
                            <th style="padding: 12px; text-align: right;">النوع</th>
                            <th style="padding: 12px; text-align: right;">سعر الشراء</th>
                            <th style="padding: 12px; text-align: right;">سعر الكراء</th>
                            <th style="padding: 12px; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 12px;"><?php echo htmlspecialchars(substr($book['title'], 0, 30)); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($book['author']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($book['type_name']); ?></td>
                                <td style="padding: 12px;"><?php echo formatPrice($book['price_buy']); ?></td>
                                <td style="padding: 12px;"><?php echo formatPrice($book['price_rental']); ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <button type="submit" class="btn btn-error" style="padding: 5px 10px; font-size: 12px;">🗑️ حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="color: var(--gray);">لا توجد كتب حتى الآن</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- التذييل -->
    <footer class="footer">
        <p>&copy; 2026 مكتبة. جميع الحقوق محفوظة.</p>
    </footer>
</body>
</html>

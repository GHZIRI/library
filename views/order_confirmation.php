<?php
session_start();
require_once '../core/db.php';

$typ = $_GET['type'] ?? null;
$id  = $_GET['id']   ?? null;

if (!$typ || !$id) {
    header("Location: catalogue.php");
    exit();
}

if ($typ === 'buy') {
    $stmt = $pdo->prepare("
        SELECT purchases.*, books.title, books.author
        FROM purchases
        JOIN books ON purchases.book_id = books.id
        WHERE purchases.id = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("
        SELECT rentals.*, books.title, books.author
        FROM rentals
        JOIN books ON rentals.book_id = books.id
        WHERE rentals.id = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
}

if(!$order){
    header("Location: catalogue.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد الطلب — مكتبة الأندلس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/confirmation.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">📚 مكتبة الأندلس</div>
    <div class="nav-links">
        <a href="../index.php">الرئيسية</a>
    </div>
</nav>

<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="success-icon">✅</div>
        <h2>تم تأكيد طلبك بنجاح!</h2>
        <p>شكراً لك على اختيارك مكتبة الأندلس. إليك تفاصيل طلبك:</p>

        <div class="order-details">
            <div class="detail-row">
                <span>اسم الكتاب:</span>
                <strong><?= htmlspecialchars($order['title']) ?></strong>
            </div>
            <div class="detail-row">
                <span>نوع الطلب:</span>
                <strong><?= $typ === 'buy' ? 'شراء' : 'كراء' ?></strong>
            </div>
            <?php if ($typ === 'buy'): ?>
                <div class="detail-row">
                    <span>الكمية:</span>
                    <strong><?= $order['quantity'] ?></strong>
                </div>
            <?php else: ?>
                <div class="detail-row">
                    <span>المدة:</span>
                    <strong>من <?= date('d/m/Y', strtotime($order['rent_from'])) ?> إلى <?= date('d/m/Y', strtotime($order['rent_until'])) ?></strong>
                </div>
            <?php endif; ?>
            <div class="detail-row">
                <span>المبلغ الإجمالي:</span>
                <strong style="color: var(--color-primary);"><?= number_format($order['total_price'], 2) ?> درهم</strong>
            </div>
        </div>

        <div class="btn-group">
            <a href="catalogue.php" class="btn btn-primary">العودة للمتجر</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="user_dashboard.php" class="btn" style="background: var(--color-light-bg);">لوحة التحكم</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
    <p>📚 جميع الحقوق محفوظة لمكتبة الأندلس © <?= date('Y') ?></p>
</footer>

</body>
</html>

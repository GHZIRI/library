<?php
session_start();
require_once '../core/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT purchases.*, books.title, books.author
    FROM purchases
    JOIN books ON purchases.book_id = books.id
    WHERE purchases.user_id = ?
    ORDER BY purchases.purchased_at DESC
");
$stmt->execute([$user_id]);
$purchases = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT rentals.*, books.title, books.author, books.id as book_id
    FROM rentals
    JOIN books ON rentals.book_id = books.id
    WHERE rentals.user_id = ?
    ORDER BY rentals.created_at DESC
");
$stmt->execute([$user_id]);
$rentals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حسابي — مكتبة الأندلس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">📚 مكتبة الأندلس</div>
    <div class="nav-links">
        <a href="../index.php">الرئيسية</a>
        <a href="../index.php">الكتالوج</a>
        <a href="../core/logout.php">تسجيل الخروج</a>
    </div>
</nav>

<div class="dashboard-container">
    <div class="welcome-section">
        <h2>مرحباً، <?= htmlspecialchars($user['full_name']) ?> 👋</h2>
        <p>هنا تجد سجل مشترياتك وكتبك المستعارة</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= count($purchases) ?></h3>
            <p>مشتريات</p>
        </div>
        <div class="stat-card">
            <h3><?= count($rentals) ?></h3>
            <p>كتب مستعارة</p>
        </div>
    </div>

    <div class="history-section">
        <h3>📚 كتبي المستعارة</h3>
        <?php if(empty($rentals)): ?>
            <p>لا توجد اشتراكات حالياً. <a href="../index.php">تصفح الكتالوج</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الكتاب</th>
                        <th>من تاريخ</th>
                        <th>إلى تاريخ</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rentals as $rental): ?>
                        <?php 
                            $is_active = $rental['paid'] == 1 && $rental['rent_until'] >= date('Y-m-d');
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($rental['title']) ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($rental['rent_from'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($rental['rent_until'])) ?></td>
                            <td>
                                <?php if($is_active): ?>
                                    <span class="status-badge status-active">سارٍ</span>
                                <?php else: ?>
                                    <span class="status-badge status-expired">منتهٍ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($is_active): ?>
                                    <a href="read_book.php?id=<?= $rental['book_id'] ?>" class="btn-read-small">📖 قراءة</a>
                                <?php else: ?>
                                    <a href="rent.php?id=<?= $rental['book_id'] ?>" style="color: var(--color-primary); font-size: 13px;">تجديد</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="history-section">
        <h3>🛍️ سجل المشتريات</h3>
        <?php if(empty($purchases)): ?>
            <p>لا توجد مشتريات سابقة.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الكتاب</th>
                        <th>الكمية</th>
                        <th>الإجمالي</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($purchases as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['title']) ?></td>
                            <td><?= $p['quantity'] ?></td>
                            <td><?= number_format($p['total_price'], 2) ?> درهم</td>
                            <td><?= date('d/m/Y', strtotime($p['purchased_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <p>📚 جميع الحقوق محفوظة لمكتبة الأندلس © <?= date('Y') ?></p>
</footer>

</body>
</html>

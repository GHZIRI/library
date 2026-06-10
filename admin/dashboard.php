<?php
session_start();
require_once '../core/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT COUNT(*) FROM books");
$total_books = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
$recent_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الإدارة — مكتبة الأندلس</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">🛠️ إدارة المكتبة</div>
    <div class="nav-links">
        <a href="../index.php">معاينة الموقع</a>
        <a href="../core/logout.php" style="background: var(--color-danger); color: white;">خروج</a>
    </div>
</nav>

<div class="admin-container">
    <aside class="admin-sidebar">
        <h2>القائمة الرئيسية</h2>
        <nav class="admin-nav">
            <a href="#" class="active">🏠 الرئيسية</a>
            <a href="#">📚 إدارة الكتب</a>
            <a href="#">👥 المستخدمين</a>
            <a href="#">🛒 الطلبات</a>
            <a href="#">⚙️ الإعدادات</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h2>لوحة الإحصائيات</h2>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div style="background: var(--color-light-bg); padding: 30px; border-radius: 12px; text-align: center;">
                <h3 style="font-size: 32px; color: var(--color-primary);"><?= $total_books ?></h3>
                <p>إجمالي الكتب</p>
            </div>
            <div style="background: var(--color-light-bg); padding: 30px; border-radius: 12px; text-align: center;">
                <h3 style="font-size: 32px; color: var(--color-secondary);"><?= $total_users ?></h3>
                <p>المشتركين</p>
            </div>
        </div>

        <section>
            <h3>آخر المستخدمين المسجلين</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الرتبة</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= $u['role'] ?>">
                                    <?= $u['role'] === 'admin' ? 'مدير' : 'مستخدم' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</body>
</html>

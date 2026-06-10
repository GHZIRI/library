<?php
session_start();
require_once '../core/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$book_id = $_GET['id'] ?? null;
if (!$book_id || !is_numeric($book_id)) {
    header("Location: catalogue.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, title, author, content FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: catalogue.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT id, rent_until
    FROM rentals
    WHERE user_id    = ?
      AND book_id    = ?
      AND paid       = 1
      AND rent_until >= CURDATE()
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id'], $book_id]);
$rental = $stmt->fetch();

if (!$rental) {
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول غير مصرح — <?= htmlspecialchars($book['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared.css">
</head>
<body style="background: var(--color-light-bg); display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow-lg); text-align: center; max-width: 500px;">
        <div style="font-size: 60px;">🔒</div>
        <h2 style="margin: 20px 0;">يجب كراء الكتاب أولاً</h2>
        <p style="color: var(--color-gray); margin-bottom: 30px;">نعتذر، ولكن قراءة هذا الكتاب متاحة فقط للمشتركين الذين لديهم استعارة سارية ومؤداة.</p>
        <a href="rent.php?id=<?= $book['id'] ?>" class="btn btn-primary">📖 كراء الكتاب الآن</a>
        <br><br>
        <a href="catalogue.php" style="color: var(--color-gray); font-size: 14px;">العودة للكتالوج</a>
    </div>
</body>
</html>
<?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قراءة: <?= htmlspecialchars($book['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared.css">
    <link rel="stylesheet" href="../assets/css/read.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">📚 مكتبة الأندلس</div>
    <div class="nav-links">
        <a href="catalogue.php">الكتالوج</a>
        <a href="user_dashboard.php">حسابي</a>
        <a href="../core/logout.php">خروج</a>
    </div>
</nav>

<main class="read-container">
    <div class="read-header">
        <h1><?= htmlspecialchars($book['title']) ?></h1>
        <p>بـقـلم: <?= htmlspecialchars($book['author']) ?></p>
        <div style="margin-top: 10px; color: var(--color-success); font-weight: 700; font-size: 13px;">
            ✅ اشتراكك سارٍ حتى: <?= date('d/m/Y', strtotime($rental['rent_until'])) ?>
        </div>
    </div>
    
    <article class="read-content">
        <?= nl2br(htmlspecialchars($book['content'] ?? 'المحتوى غير متوفر حالياً.')) ?>
    </article>

    <div style="text-align: center; margin-top: 40px;">
        <a href="user_dashboard.php" class="btn btn-primary">⬅ العودة إلى لوحة التحكم</a>
    </div>
</main>

<footer class="footer">
    <p>📚 جميع الحقوق محفوظة لمكتبة الأندلس © <?= date('Y') ?></p>
</footer>

</body>
</html>

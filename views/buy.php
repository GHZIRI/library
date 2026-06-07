<?php
session_start();
require_once '../core/db.php';

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit();
}

$book_id = $_GET['id'] ?? null;
if(!$book_id){
   header("location: catalogue.php");
   exit();
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt = execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: catalogue.php");
    exit();
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy - <?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <h1>📚 Library</h1>
    <div class="nav-links">
        <a href="catalogue.php">Catalogue</a>
        <a href="user_dashboard.php">My Account</a>
        <a href="../core/logout.php">Logout</a>
    </div>
</nav>

<div class="buy-container">

    <h2>🛒 Buy Book</h2>

    <!-- معلومات الكتاب -->
    <div class="book-summary">
        <h3><?php htmlspecialchars($book['title']) ?></h3>
        <p>✍️ <?php htmlspecialchars($book['author']) ?></p>
        <p>💰 Price: <strong><?= $book['price_buy'] ?> MAD</strong></p>
        <p>📦 Stock: <strong><?= $book['stock'] ?></strong></p>
    </div>

    <?php
    // عرض الأخطاء
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p class='error'>❌ {$error}</p>";
        }
    }

    // عرض النجاح
    if (isset($_GET['success'])) {
        echo "<p class='success'>✅ Purchase successful! <a href='user_dashboard.php'>View your orders</a></p>";
    }
    ?>

    <!-- إذا الكتاب في المخزون نعرض الفورم -->
    <?php if ($book['stock'] > 0) { ?>

        <form action="../core/functions.php" method="POST">

            <input type="hidden" name="action"  value="buy">
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity"
                       min="1" max="<?= $book['stock'] ?>"
                       value="1" required>
            </div>

            <button type="submit" class="btn-primary">🛒 Confirm Purchase</button>

        </form>

    <?php } else { ?>
        <p class="out-stock">❌ This book is out of stock.</p>
    <?php } ?>

    <a href="catalogue.php" class="btn-back">⬅️ Back to Catalogue</a>

</div>

</body>
</html>
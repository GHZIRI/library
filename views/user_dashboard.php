<?php
session_start();
require_once '../core/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php"); // Fix: "locatin" → "Location"
    exit();
}

$user_id = $_SESSION['user_id']; // Fix: $_SESSION('user_id') → $_SESSION['user_id'] (Fatal Error)

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT purchases.*, books.title, books.author, books.cover_image
    FROM purchases
    JOIN books ON purchases.book_id = books.id
    WHERE purchases.user_id = ?
    ORDER BY purchases.purchased_at DESC
");
$stmt->execute([$user_id]);
$purchases = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT rentals.*, books.title, books.author, books.cover_image
    FROM rentals
    JOIN books ON rentals.book_id = books.id
    WHERE rentals.user_id = ?
    ORDER BY rentals.created_at DESC
");
$stmt->execute([$user_id]);
$rentals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Library</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Fix: CSS was missing -->
</head>
<body>
    <nav class="navbar">
        <h1>📚 Library</h1>
        <div class="nav-links">
            <a href="catalogue.php">Catalogue</a>
            <a href="user_dashboard.php">My Account</a>
            <a href="../core/logout.php">Logout</a>
        </div>
    </nav>

    <div class="stast-grid">
        <div class="stat-card">
            <h3><?= count($purchases) ?></h3> <!-- Fix: missing echo -->
            <p>Total Purchases</p>
        </div>
        <div class="stat-card">
            <h3><?= count($rentals) ?></h3> <!-- Fix: missing echo -->
            <p>Total Rentals</p>
        </div>
    </div>

    <div class="section">
        <h2>My Purchases</h2>
        <?php if(empty($purchases)): ?>
            <p>No purchases yet. <a href="catalogue.php">Browse books</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($purchases as $purchase): ?>
                        <tr>
                            <td><?= htmlspecialchars($purchase['title']) ?></td>
                            <td><?= htmlspecialchars($purchase['author']) ?></td>
                            <td><?= $purchase['quantity'] ?></td>
                            <td><?= $purchase['total_price'] ?> MAD</td>
                            <td><?= date('d/m/Y', strtotime($purchase['purchased_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>My Rentals</h2>
        <?php if(empty($rentals)): ?>
            <p>No rentals yet. <a href="catalogue.php">Browse books</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الكتاب</th>
                        <th>المؤلف</th>
                        <th>من</th>
                        <th>حتى</th>
                        <th>الإجمالي</th>
                        <th>الحالة</th>
                        <th>قراءة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rentals as $rental): ?>
                        <?php
                            $is_active_rental = $rental['paid'] == 1
                                && $rental['status'] === 'active'
                                && $rental['rent_until'] >= date('Y-m-d');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($rental['title']) ?></td>
                            <td><?= htmlspecialchars($rental['author']) ?></td>
                            <td><?= date('d/m/Y', strtotime($rental['rent_from'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($rental['rent_until'])) ?></td>
                            <td><?= $rental['total_price'] ?> MAD</td>
                            <td>
                                <?php if($is_active_rental): ?>
                                    <span class="status-active">Active</span>
                                <?php elseif($rental['rent_until'] < date('Y-m-d')): ?>
                                    <span class="status-returned">Expired</span>
                                <?php else: ?>
                                    <span class="status-returned">Returned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($is_active_rental): ?>
                                    <a href="read_book.php?id=<?= $rental['book_id'] ?>" style="color: #2e7d32; font-weight: 600;">📖 اقرأ</a>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 13px;">غير مدفوع</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>

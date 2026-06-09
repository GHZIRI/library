<?php
session_start();
require_once '../core/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$typ = $_GET['type'] ?? null; // Fix: $_GET('type') → $_GET['type']  (Fatal Error)
$id  = $_GET['id']   ?? null;

if(!$typ || !$id){
    header("Location: catalogue.php");
    exit();
}

if($typ === 'buy'){
    $stmt = $pdo->prepare("
        SELECT purchases.*, books.title, books.author
        FROM purchases
        JOIN books ON purchases.book_id = books.id
        WHERE purchases.id = ?
        AND purchases.user_id = ?
    ");
    $stmt->execute([$id, $_SESSION['user_id']]); // Fix: was execute($id, ...) missing array wrapper
    $order = $stmt->fetch();
}

if($typ === 'rent'){
    $stmt = $pdo->prepare("
        SELECT rentals.*, books.title, books.author
        FROM rentals
        JOIN books ON rentals.book_id = books.id
        WHERE rentals.id = ?
        AND rentals.user_id = ?
    ");
    $stmt->execute([$id, $_SESSION['user_id']]); // Fix: was execute([$_SESSION['user_id']]) missing $id
    $order = $stmt->fetch();
}

if(!isset($order) || !$order){
    header("Location: catalogue.php"); // Fix: "catalohue.php" → "catalogue.php"
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Library</title>
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

    <div class="cornfirmation">
        <div class="confirmation-box">
            <h2>✅ Order Confirmed!</h2>
            <p>Thank you for your order.</p>
        </div>

        <div class="detail-row">
            <span>Book:</span>
            <strong><?= htmlspecialchars($order['title']) ?></strong> <!-- Fix: missing echo -->
        </div>

        <div class="detail-row">
            <span>Author:</span>
            <strong><?= htmlspecialchars($order['author']) ?></strong> <!-- Fix: missing echo -->
        </div>

        <?php if($typ === 'buy'): ?>
            <div class="detail-row">
                <span>Quantity:</span>
                <strong><?= $order['quantity'] ?></strong>
            </div>
            <div class="detail-row">
                <span>Total Price:</span>
                <strong><?= $order['total_price'] ?> MAD</strong>
            </div>
            <div class="detail-row">
                <span>Date:</span>
                <strong><?= date('d/m/Y', strtotime($order['purchased_at'])) ?></strong>
            </div>
        <?php endif; ?>

        <?php if($typ === 'rent'): ?> <!-- Fix: $type → $typ (undefined variable) -->
            <div class="detail-row">
                <span>From:</span>
                <strong><?= date('d/m/Y', strtotime($order['rent_from'])) ?></strong> <!-- Fix: missing echo -->
            </div>
            <div class="detail-row">
                <span>Until:</span>
                <strong><?= date('d/m/Y', strtotime($order['rent_until'])) ?></strong> <!-- Fix: missing echo -->
            </div>
            <div class="detail-row">
                <span>Total Price:</span>
                <strong><?= $order['total_price'] ?> MAD</strong> <!-- Fix: missing echo -->
            </div>
            <div class="detail-row">
                <span>Status:</span>
                <strong class="status-active">Active</strong>
            </div>
        <?php endif; ?>

        <div class="confirmation-actions">
            <a href="catalogue.php" class="btn-primary">Continue Shopping</a>
            <a href="user_dashboard.php" class="btn-secondary">My Account</a>
        </div>
    </div>

</body>
</html>

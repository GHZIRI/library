<?php
session_start();
require_once '../core/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM books ORDER BY created_at DESC");
$stmt->execute();
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - Library</title>
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

    <div class="catalogue-container">
        <h2>All Books</h2>

        <?php if(empty($books)): ?>
            <p>No books available.</p>
        <?php endif; ?>

        <div class="books-grid">
            <?php foreach($books as $book): ?> <!-- Fix: foreach was closed too early -->
                <div class="book-card">
                    <?php if(!empty($book['cover_image'])): ?>
                        <img src="../assets/images/<?= htmlspecialchars($book['cover_image']) ?>" alt="cover">
                    <?php else: ?>
                        <div class="no-image"></div>
                    <?php endif; ?>

                    <div class="book-info">
                        <h3><?= htmlspecialchars($book['title']) ?></h3> <!-- Fix: missing echo -->
                        <p class="author"><?= htmlspecialchars($book['author']) ?></p> <!-- Fix: missing echo -->
                        <p class="category"><?= htmlspecialchars($book['category'] ?? '') ?></p>
                        <p class="description">
                            <?= htmlspecialchars(mb_substr($book['description'] ?? '', 0, 100)) ?>
                        </p>
                    </div>

                    <div class="book-proced">
                        <span class="price-buy">Buy: <?= $book['price_buy'] ?> MAD</span>   <!-- Fix: price-buy → price_buy -->
                        <span class="price-rent">Rent: <?= $book['price_rent'] ?> MAD/day</span> <!-- Fix: price-rent → price_rent -->
                    </div>

                    <?php if($book['stock'] > 0): ?>
                        <p class="in-stock">In stock (<?= $book['stock'] ?>)</p> <!-- Fix: typos in class and tag -->
                    <?php else: ?>
                        <p class="out-stock">Out of Stock</p>
                    <?php endif; ?>

                    <div class="book-action">
                        <a href="buy.php?id=<?= $book['id'] ?>" class="btn-buy">Buy</a>
                        <a href="rent.php?id=<?= $book['id'] ?>" class="btn-rent">Rent</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
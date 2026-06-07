<?php
session_start();
require_once '../core/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$stmt = $pdo->prepare("SELECT * FROM books ORDER BY created_at DESC ");
$stmt->execute();
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <a href="catalogue.php">Catalogue</a>
            <a href="user_dashboard.php">My Account</a>
            <a href="../core/logout.php">Logout</a>
        </div>
        <div class="catalogue-container">
            <h2>All Books</h2>
            <?php 
            if(empty($books)){?>
            <p>No books available. </p>
            <?php } ?>
            <div class="books-grid">
                <?php 
                foreach($books as $book) {
                ?>
                <div class="book-card">
                    <?php 
                     if(!empty($book['cover_image'])){
                         echo "<img src='../assets/images/{$book['cover_image']}' alt='cover'>";
                     } else {
                    echo "<div class='no-image'>📖</div>";
                     }
                     }
                    ?>

                    <div class="book-info">
                        <h3><?php htmlspecialchars($book["title"]) ?></h3>
                        <p class="author"><?php htmlspecialchars($book['author']) ?></p>
                        <p class="category"><?php htmlspecialchars($book['category']) ?></p>
                        <p class="description">
                            <?php htmlspecialchars($book['descriptopn'] ?? '',0,100) ?>
                        </p>
                    </div>
                    <div class="book-proced">
                        <span class="price-buy" >Buy: <?php $book['price-buy']?>MAD</span>
                        <span class="price-rent">Rent: <?php $book['price-rent'] ?> MAD/day</span>
                    </div>
                     <?php if($book["stock"] > 0){
                        echo "<p calass='in-stypck'> In stock ({$book['stock']}<p/>";
                     }else{
                        echo "<p class='out-stock'> Out of Stock</p>";
                     } 
                     ?>
                     <div class="book-action">
                          <a href="buy.php?id=<?= $book['id'] ?>" class="btn-buy"> Buy</a>
                          <a href="rent.php?id=<?= $book['id'] ?>" class="btn-rent"> Rent</a> 
                     </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </nav>
</body>
</html>
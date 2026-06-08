<?php
session_start();
require_once '../core/db.php';

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit();
}

$typ = $_GET('type') ?? null;
$id = $_GET['id'] ?? null;
if(!$typ || !$id){
    header("location: catalogue.php");
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
 $stmt->execute($id, $_SESSION['user_id']);
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
    $stmt->execute([$_SESSION['user_id']]);
    $order = $stmt->fetch();
}
if(!$order){
    header("location: catalohue.php");
    exit();
}
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
        <h1>Library</h1>
        <div class="nav-links">
            <a href="catalogue.php">Catalogue</a>
            <a href="user_dashboard.php">My Account</a>
            <a href="../core/logout.php">Logout</a>
        </div>
      </nav>
      <div class="cornfirmation">
        <div class="confirmation-box">
            <h2>Order Corfirmed!</h2>
            <p>Think you for your Order</p>
        </div>
        <div class="detail-row">
            <span>Book:</span>
            <strong><?php htmlspecialchars($order['title']) ?></strong>
        </div>
         <div class="detail-row">
                <span>Author:</span>
                <strong><?php htmlspecialchars($order['author']) ?></strong>
            </div>

            <?php  
              if($typ === 'buy'){};
            ?>
             <div class="detail-row">
                    <span>Quantity:</span>
                    <strong><?= $order['quantity'] ?></strong>
                </div>

                <div class="detail-row">
                    <span>Total Price:</span>
                    <strong><?= $order['total_price'] ?> MAD</strong>
                </div>

                <!-- date() تحول التاريخ من 2024-01-15 إلى 15/01/2024 -->
                <div class="detail-row">
                    <span>Date:</span>
                    <strong><?= date('d/m/Y', strtotime($order['purchased_at'])) ?></strong>
                </div>

            
            <?php if ($type === 'rent') { ?>

                <div class="detail-row">
                    <span>From:</span>
                    <strong><?php date('d/m/Y', strtotime($order['rent_from'])) ?></strong>
                </div>

                <div class="detail-row">
                    <span>Until:</span>
                    <strong><?php date('d/m/Y', strtotime($order['rent_until'])) ?></strong>
                </div>

                <div class="detail-row">
                    <span>Total Price:</span>
                    <strong><?php $order['total_price'] ?> MAD</strong>
                </div>

                <div class="detail-row">
                    <span>Status:</span>
                    <strong class="status-active"> Active</strong>
                </div>

            <?php } ?>

        </div>

        
        <div class="confirmation-actions">
            <a href="catalogue.php" class="btn-primary"> Continue Shopping</a>
            <a href="user_dashboard.php" class="btn-secondary"> My Account</a>
        </div>

    </div>
</div>

</body>
</html>

      </div>
</body>
</html>



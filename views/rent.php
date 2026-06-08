<?php
session_start();
require_once '../core/db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$book_id = $_GET['id'] ?? null;


if (!$book_id) {
    header("Location: catalogue.php");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();


if (!$book) {
    header("Location: catalogue.php");
    exit();
}


$today   = date('Y-m-d');
$max_date = date('Y-m-d', strtotime('+30 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent - <?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

<div class="rent-container">

    <h2> Rent Book</h2>

    
    <div class="book-summary">
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <p> <?= htmlspecialchars($book['author']) ?></p>
        <p> Price per day: <strong><?= $book['price_rent'] ?> MAD</strong></p>
        <p> Stock: <strong><?= $book['stock'] ?></strong></p>
    </div>

    <?php
  
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p class='error'> {$error}</p>";
        }
    }

    
    if (isset($_GET['success'])) {
        echo "<p class='success'> Rental confirmed! <a href='user_dashboard.php'>View your rentals</a></p>";
    }
    ?>

    
    <?php if ($book['stock'] > 0) { ?>

        <form action="../core/functions.php" method="POST">

            <input type="hidden" name="action"  value="rent">
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

            <!-- تاريخ البداية -->
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="rent_from"
                       min="<?= $today ?>"
                       value="<?= $today ?>"
                       required>
            </div>

            
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="rent_until"
                       min="<?= $today ?>"
                       max="<?= $max_date ?>"
                       required>
            </div>

          
            <div class="price-calculator">
                <p>Price per day: <strong><?= $book['price_rent'] ?> MAD</strong></p>
                <p>Total: <strong id="total-price">0 MAD</strong></p>
            </div>

            <button type="submit" class="btn-primary"> Confirm Rental</button>

        </form>

    <?php } else { ?>
        <p class="out-stock"> This book is out of stock.</p>
    <?php } ?>

    <a href="catalogue.php" class="btn-back">⬅ Back to Catalogue</a>

</div>


<script>
    const pricePerDay = <?= $book['price_rent'] ?>;
    const fromInput   = document.querySelector('input[name="rent_from"]');
    const untilInput  = document.querySelector('input[name="rent_until"]');
    const totalDiv    = document.getElementById('total-price');

    function calculatePrice() {
        const from  = new Date(fromInput.value);
        const until = new Date(untilInput.value);

       
        const days = Math.ceil((until - from) / (1000 * 60 * 60 * 24));

        if (days > 0) {
            totalDiv.textContent = (days * pricePerDay) + ' MAD';
        } else {
            totalDiv.textContent = '0 MAD';
        }
    }

    fromInput.addEventListener('change', calculatePrice);
    untilInput.addEventListener('change', calculatePrice);
</script>

</body>
</html>
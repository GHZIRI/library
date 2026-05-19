<?php
require_once '../core/functions.php';

// If not logged in, redirect to login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user id
$user_id = currentUser()['id_user'];

// Get all cart items from database
$cartItems = getCart($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart — Library</title>
</head>
<body>

    <h1>My Cart</h1>

    <!-- If cart is empty -->
    <?php if (empty($cartItems)) { ?>
        <p>Your cart is empty. <a href="catalogue.php">Browse books</a></p>

    <?php } else { ?>

        <?php foreach ($cartItems as $item) { ?>
            <div id="cart-item-<?= $item['id_cart'] ?>">

                <!-- Book info loaded by JavaScript -->
                <div id="book-<?= $item['book_id'] ?>">
                    <p>Loading...</p>
                </div>

                <p>Type: <?= $item['type'] ?></p>

                <?php if ($item['type'] === 'rental') { ?>
                    <p>Months: <?= $item['rental_months'] ?></p>
                <?php } ?>

                <button onclick="removeItem(<?= $item['id_cart'] ?>)">Remove</button>

            </div>
        <?php } ?>

        <a href="checkout.php">Proceed to Checkout</a>

    <?php } ?>

    <script src="../assets/js/cart.js"></script>

</body>
</html>
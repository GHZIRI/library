<?php
require_once '../core/functions.php';

// If not logged in, send to login page
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get the current user id from session
$user_id = currentUser()['id_user'];

// Get all books in the cart from database
$cartItems = getCart($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart — Library</title>
</head>
<body>

    <h1>My Cart</h1>

    <!-- If cart is empty -->
    <?php if (empty($cartItems)) { ?>
        <p>Your cart is empty. <a href="catalogue.php">Browse books</a></p>

    <?php } else { ?>

        <!-- Loop through each item in the cart -->
        <?php foreach ($cartItems as $item) { ?>
            <div id="cart-item-<?= $item['id_cart'] ?>">

                <!-- Book info will be loaded by JavaScript -->
                <div id="book-<?= $item['book_id'] ?>">
                    <p>Loading...</p>
                </div>

                <!-- Show if it's a buy or rental -->
                <p>Type: <?= $item['type'] ?></p>

                <!-- Show rental months if it's a rental -->
                <?php if ($item['type'] === 'rental') { ?>
                    <p>Months: <?= $item['rental_months'] ?></p>
                <?php } ?>

                <!-- Remove from cart button -->
                <button onclick="removeItem(<?= $item['id_cart'] ?>)">Remove</button>

            </div>
        <?php } ?>

        <!-- Go to checkout -->
        <a href="checkout.php">Proceed to Checkout</a>

    <?php } ?>

    <script src="../assets/js/cart.js"></script>
</body>
</html>
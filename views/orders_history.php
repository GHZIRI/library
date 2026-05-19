<?php
require_once '../core/functions.php';

// If not logged in, redirect to login
if (!isLoggedIn()) {
    redirect('login.php');
}
    $user_id = currentUser()['login.php'];

// Get all orders (buy + rental)
$orders = getUserOrders($user_id);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — Library</title>
</head>
<body>

    <h1>My Orders</h1>

    <!-- Buy Orders -->
    <h2>📚 Bought Books</h2>
    <?php if (empty($orders['buy'])) { ?>
        <p>No buy orders yet.</p>
    <?php } else { ?>
        <?php foreach ($orders['buy'] as $order) { ?>
            <div>
                <p><b>Book ID:</b> <?= $order['book_id'] ?></p>
                <p><b>Name:</b> <?= $order['name_buy'] ?></p>
                <p><b>City:</b> <?= $order['city'] ?></p>
                <p><b>Phone:</b> <?= $order['phone_number'] ?></p>
                <p><b>Total:</b> <?= $order['total_price'] ?> DH</p>
                <p><b>Status:</b> <?= $order['status'] ?></p>
                <p><b>Date:</b> <?= $order['created_at'] ?></p>
                <hr>
            </div>
        <?php } ?>
    <?php } ?>

    <!-- Rental Orders -->
    <h2>📖 Rented Books</h2>
    <?php if (empty($orders['rental'])) { ?>
        <p>No rental orders yet.</p>
    <?php } else { ?>
        <?php foreach ($orders['rental'] as $order) { ?>
            <div>
                <p><b>Book ID:</b> <?= $order['book_id'] ?></p>
                <p><b>Name:</b> <?= $order['name_rental'] ?></p>
                <p><b>City:</b> <?= $order['city'] ?></p>
                <p><b>Phone:</b> <?= $order['phone_number'] ?></p>
                <p><b>Months:</b> <?= $order['rental_months'] ?></p>
                <p><b>Total:</b> <?= $order['total_price'] ?> DH</p>
                <p><b>Start:</b> <?= $order['start_date'] ?></p>
                <p><b>End:</b> <?= $order['end_date'] ?></p>
                <p><b>Status:</b> <?= $order['status'] ?></p>
                <hr>
            </div>
        <?php } ?>
    <?php } ?>

    <a href="catalogue.php">Back to Catalogue</a>

</body>
</html>




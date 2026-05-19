<?php
require_once '../core/functions.php';


// If not logged in, redirect to login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user info

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — Library</title>
</head>
<body>

    <h1>Welcome, <?= $user['name_user'] ?>!</h1>

    <!-- User Info -->
    <h2>My Info</h2>
    <p><b>Name:</b> <?= $user['name_user'] ?></p>
    <p><b>Email:</b> <?= $user['email'] ?></p>

    <!-- Quick Links -->
    <h2>Quick Links</h2>
    <a href="catalogue.php">📚 Browse Books</a><br>
    <a href="cart.php">🛒 My Cart</a><br>
    <a href="orders_history.php">📋 My Orders</a><br>
    <a href="../core/logout.php">🚪 Logout</a>

</body>
</html>


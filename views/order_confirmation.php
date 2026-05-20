<?php
require_once '../core/functions.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed — Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar__inner">
        <a href="catalogue.php" class="navbar__brand">📚 <span>Library</span></a>
        <div class="navbar__links">
            <a href="orders_history.php">📋 My Orders</a>
            <a href="../core/logout.php" class="btn-nav-logout">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="confirm-screen">
    <div class="confirm-screen__icon">✅</div>
    <h1>Order Confirmed!</h1>
    <p>Thank you! Your order has been placed successfully and is now being processed.</p>
    <div class="confirm-screen__actions">
        <a href="catalogue.php" class="btn btn-primary">Continue Shopping</a>
        <a href="orders_history.php" class="btn btn-secondary">View My Orders</a>
    </div>
</div>

</body>
</html>
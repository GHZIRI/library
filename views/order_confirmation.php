<?php
require_once '../core/functions.php';


// If not logged in, redirect to login
if(!isLoggedIn()){
    redirect("login.php");
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
     <h1>✅ Order Confirmed!</h1>
    <p>Thank you! Your order has been placed successfully.</p>
    <a href="catalogue.php">Continue Shopping</a>
    <a href="orders_history.php">View My Orders</a>

</body>
</html>
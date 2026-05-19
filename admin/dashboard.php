<?php
require_once '../core/functions.php';

// If not logged in → login
if (!isLoggedIn()) {
    redirect('../views/login.php');
}

// If not admin → catalogue
if (!isAdmin()) {
    redirect('../views/catalogue.php');
}

// Get total users count
$users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get total buy orders count
$buy_orders = $pdo->query("SELECT COUNT(*) FROM orders_buy")->fetchColumn();

// Get total rental orders count
$rental_orders = $pdo->query("SELECT COUNT(*) FROM orders_rental")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Library</title>
</head>
<body>

    <h1>Admin Dashboard</h1>

    <h2>📊 Statistics</h2>
    <p><b>Total Users:</b> <?= $users ?></p>
    <p><b>Total Buy Orders:</b> <?= $buy_orders ?></p>
    <p><b>Total Rental Orders:</b> <?= $rental_orders ?></p>

    <h2>Quick Links</h2>
    <a href="manage_users.php">👥 Manage Users</a><br>
    <a href="manage_orders.php">📋 Manage Orders</a><br>
    <a href="manage_books.php">📚 Manage Books</a><br>
    <a href="../views/catalogue.php">🌐 View Site</a><br>
    <a href="../views/login.php">🚪 Logout</a>

</body>
</html>
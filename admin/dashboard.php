<?php
session_start();
require_once '../core/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../views/catalogue.php");
    exit();
}




$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stmt->execute();
$total_users = $stmt->fetch()['total'];

// Total books count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM books");
$stmt->execute();
$total_books = $stmt->fetch()['total'];

// Total purchases count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM purchases");
$stmt->execute();
$total_purchases = $stmt->fetch()['total'];

// Total rentals count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM rentals");
$stmt->execute();
$total_rentals = $stmt->fetch()['total'];

// Total revenue from purchases
$stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM purchases");
$stmt->execute();
$total_revenue = $stmt->fetch()['total'] ?? 0;


$stmt = $pdo->prepare("
    SELECT purchases.*, COALESCE(purchases.full_name, users.full_name, 'Guest') AS customer_name, books.title
    FROM purchases
    LEFT JOIN users ON purchases.user_id = users.id
    JOIN books ON purchases.book_id = books.id
    ORDER BY purchases.purchased_at DESC
    LIMIT 5
");
$stmt->execute();
$last_purchases = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT * FROM users
    WHERE role = 'user'
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute();
$last_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Fix: unified CSS -->
</head>
<body>


<nav class="navbar">
    <h1> Library Admin</h1>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="../core/logout.php">Logout</a>
    </div>
</nav>

<div class="admin-container">

    <h2> Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>

    <div class="stats-grid">

        <div class="stat-card">
            <h3><?= $total_users ?></h3>
            <p> Total Users</p>
        </div>

        <div class="stat-card">
            <h3><?= $total_books ?></h3>
            <p> Total Books</p>
        </div>

        <div class="stat-card">
            <h3><?= $total_purchases ?></h3>
            <p> Total Purchases</p>
        </div>

        <div class="stat-card">
            <h3><?= $total_rentals ?></h3>
            <p> Total Rentals</p>
        </div>

        <div class="stat-card">
            <h3><?= number_format($total_revenue, 2) ?> MAD</h3>
            <p> Total Revenue</p>
        </div>

    </div>


    <div class="section">
        <h2> Last Purchases</h2>

        <?php if (empty($last_purchases)) { ?>
            <p>No purchases yet.</p>
        <?php } else { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($last_purchases as $purchase) { ?>
                        <tr>
                            <td><?= htmlspecialchars($purchase['customer_name']) ?></td>
                            <td><?= htmlspecialchars($purchase['title']) ?></td>
                            <td><?= $purchase['quantity'] ?></td>
                            <td><?= $purchase['total_price'] ?> MAD</td>
                            <td><?= date('d/m/Y', strtotime($purchase['purchased_at'])) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>

   
    <div class="section">
        <h2> Last Users</h2>

        <?php if (empty($last_users)) { ?>
            <p>No users yet.</p>
        <?php } else { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($last_users as $user) { ?>
                        <tr>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>

</div>

</body>
</html>

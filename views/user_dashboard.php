<?php
require_once '../core/functions.php';

requireLogin();

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar__inner">
        <a href="catalogue.php" class="navbar__brand">📚 <span>Library</span></a>
        <div class="navbar__links">
            <a href="catalogue.php">📚 Catalogue</a>
            <a href="cart.php">🛒 Cart</a>
            <a href="../core/logout.php" class="btn-nav-logout">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="page-header">
    <h1>Welcome, <?= sanitize($user['name_user']) ?>! 👋</h1>
    <p>Manage your account and orders from here</p>
</div>

<!-- User Info Card -->
<p class="section-title">👤 My Info</p>
<div style="max-width:1200px;margin:0 auto;padding:0 1.5rem 1rem;">
    <div class="card" style="max-width:480px;">
        <div class="form-group" style="margin:0;">
            <label>Full Name</label>
            <p style="color:var(--text-primary);font-weight:600;padding:.5rem 0;">
                <?= sanitize($user['name_user']) ?>
            </p>
        </div>
        <div class="form-group" style="margin:0;margin-top:.75rem;">
            <label>Email</label>
            <p style="color:var(--text-primary);font-weight:600;padding:.5rem 0;">
                <?= sanitize($user['email']) ?>
            </p>
        </div>
        <div class="form-group" style="margin:0;margin-top:.75rem;">
            <label>Role</label>
            <p>
                <span class="badge badge-<?= $user['role'] === 'admin' ? 'confirmed' : 'active' ?>">
                    <?= ucfirst($user['role']) ?>
                </span>
            </p>
        </div>
    </div>
</div>

<!-- Quick Links -->
<p class="section-title">⚡ Quick Links</p>
<div class="quick-links">
    <a href="catalogue.php"     class="btn btn-secondary">📚 Browse Books</a>
    <a href="cart.php"          class="btn btn-secondary">🛒 My Cart</a>
    <a href="orders_history.php"class="btn btn-secondary">📋 My Orders</a>
    <a href="../core/logout.php" class="btn btn-danger">🚪 Logout</a>
</div>

</body>
</html>

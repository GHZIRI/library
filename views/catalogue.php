<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue — Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar__inner">
        <a href="catalogue.php" class="navbar__brand">📚 <span>Library</span></a>
        <div class="navbar__links">
            <a href="cart.php">🛒 Cart</a>
            <a href="orders_history.php">📋 My Orders</a>
            <a href="user_dashboard.php">👤 <?= sanitize($user['name_user']) ?></a>
            <a href="../core/logout.php" class="btn-nav-logout">🚪 Logout</a>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <h1>Book Catalogue</h1>
    <p>Browse our collection of Arabic books</p>
</div>

<!-- Search Bar -->
<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search for a book...">
    <button class="btn btn-primary" onclick="searchBooks()">Search</button>
</div>

<!-- Books Grid -->
<p class="section-title">📚 Books</p>
<div class="books-grid" id="booksContainer">
    <!-- Skeleton placeholders while loading -->
    <?php for ($i = 0; $i < 8; $i++): ?>
    <div class="book-card">
        <div class="skeleton" style="width:100%;aspect-ratio:2/3;border-radius:6px;"></div>
        <div class="skeleton" style="height:14px;width:90%;margin-top:.5rem;"></div>
        <div class="skeleton" style="height:12px;width:60%;"></div>
    </div>
    <?php endfor; ?>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
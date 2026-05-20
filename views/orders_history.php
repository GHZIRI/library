<?php
require_once '../core/functions.php';

requireLogin();

$user    = currentUser();
$user_id = $user['id_user'];   // ← FIX: was wrongly set to currentUser()['login.php']

// Get all orders (buy + rental)
$orders = getUserOrders($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — Library</title>
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
            <a href="user_dashboard.php">👤 <?= sanitize($user['name_user']) ?></a>
            <a href="../core/logout.php" class="btn-nav-logout">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="page-header">
    <h1>My Orders</h1>
    <p>Track all your purchases and rentals</p>
</div>

<div class="orders-wrapper">
    
    <!-- Tabs Navigation -->
    <div class="orders-tabs">
        <button class="tab-btn active" onclick="switchTab('buy')">
            📚 Bought Books <span class="tab-count"><?= count($orders['buy']) ?></span>
        </button>
        <button class="tab-btn" onclick="switchTab('rental')">
            📖 Rented Books <span class="tab-count"><?= count($orders['rental']) ?></span>
        </button>
    </div>

    <!-- Buy Orders Section -->
    <div id="buy-section" class="orders-section active">
        <h2>📚 Your Purchases</h2>

        <?php if (empty($orders['buy'])): ?>
            <div class="empty-state">
                <p>📚 No purchases yet</p>
                <p style="color:var(--text-secondary);margin-bottom:1rem;">Start exploring our collection to buy your favorite books.</p>
                <a href="catalogue.php" class="btn btn-primary">Browse Catalogue</a>
            </div>
        <?php else: ?>
            <div class="orders-grid">
                <?php foreach ($orders['buy'] as $order): ?>
                    <div class="order-card" data-status="<?= $order['status'] ?>">
                        <div class="order-header">
                            <div>
                                <h3>Order #<?= $order['id_buy'] ?></h3>
                                <p class="order-date"><?= formatDate($order['created_at'], 'Y-m-d H:i') ?></p>
                            </div>
                            <span class="badge badge-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>

                        <div class="order-details">
                            <div class="detail-row">
                                <span class="label">Recipient:</span>
                                <span class="value"><?= sanitize($order['name_buy']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Location:</span>
                                <span class="value"><?= sanitize($order['city']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Phone:</span>
                                <span class="value"><?= sanitize($order['phone_number']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Quantity:</span>
                                <span class="value"><?= $order['quantity'] ?> book<?= $order['quantity'] !== 1 ? 's' : '' ?></span>
                            </div>
                        </div>

                        <div class="order-footer">
                            <span class="price-label">Total:</span>
                            <span class="price-value"><?= number_format($order['total_price'], 2, '.', '') ?> MAD</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rental Orders Section -->
    <div id="rental-section" class="orders-section">
        <h2>📖 Your Rentals</h2>

        <?php if (empty($orders['rental'])): ?>
            <div class="empty-state">
                <p>📖 No rentals yet</p>
                <p style="color:var(--text-secondary);margin-bottom:1rem;">Discover books to rent and save money while reading!</p>
                <a href="catalogue.php" class="btn btn-primary">Browse Catalogue</a>
            </div>
        <?php else: ?>
            <div class="orders-grid">
                <?php foreach ($orders['rental'] as $order): ?>
                    <div class="order-card rental-card" data-status="<?= $order['status'] ?>">
                        <div class="order-header">
                            <div>
                                <h3>Rental #<?= $order['id_rental'] ?></h3>
                                <p class="order-date"><?= formatDate($order['created_at'], 'Y-m-d H:i') ?></p>
                            </div>
                            <span class="badge badge-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>

                        <div class="order-details">
                            <div class="detail-row">
                                <span class="label">Recipient:</span>
                                <span class="value"><?= sanitize($order['name_rental']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Location:</span>
                                <span class="value"><?= sanitize($order['city']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Phone:</span>
                                <span class="value"><?= sanitize($order['phone_number']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Duration:</span>
                                <span class="value"><?= $order['rental_months'] ?> month<?= $order['rental_months'] !== 1 ? 's' : '' ?></span>
                            </div>
                        </div>

                        <div class="rental-dates">
                            <div class="date-block start">
                                <p class="date-label">Start</p>
                                <p class="date-value"><?= date('M d, Y', strtotime($order['start_date'])) ?></p>
                            </div>
                            <div class="date-arrow">→</div>
                            <div class="date-block end">
                                <p class="date-label">End</p>
                                <p class="date-value"><?= date('M d, Y', strtotime($order['end_date'])) ?></p>
                            </div>
                        </div>

                        <div class="order-footer">
                            <span class="price-label">Total:</span>
                            <span class="price-value"><?= number_format($order['total_price'], 2, '.', '') ?> MAD</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top:2rem;text-align:center;">
        <a href="catalogue.php" class="btn btn-secondary">← Back to Catalogue</a>
    </div>

</div>

<script>
    // Tab switching functionality
    const switchTab = (tab) => {
        // Hide all sections
        document.getElementById('buy-section').classList.remove('active');
        document.getElementById('rental-section').classList.remove('active');
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        // Show selected section
        document.getElementById(tab + '-section').classList.add('active');
        
        // Add active class to clicked button
        event.target.closest('.tab-btn').classList.add('active');
    };
</script>

</body>
</html>

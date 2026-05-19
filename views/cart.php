<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user    = currentUser();
$user_id = $user['id_user'];

// Get all cart items from database
$cartItems = getCart($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart — Library</title>
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

<div class="page-header">
    <h1>🛒 My Cart</h1>
    <p>Review your selected books before checkout</p>
</div>

<!-- Alert box -->
<div id="alertBox" style="max-width:900px;margin:0 auto;padding:0 1.5rem;"></div>

<?php if (empty($cartItems)): ?>
    <div class="cart-wrapper">
        <div class="empty-state">
            <div style="font-size:4rem;margin-bottom:1rem;">🛒</div>
            <p style="font-size:1.2rem;font-weight:600;margin-bottom:.5rem;">Your cart is empty</p>
            <p style="color:var(--text-secondary);margin-bottom:1.5rem;">Start browsing our collection to add books to your cart.</p>
            <a href="catalogue.php" class="btn btn-primary">Browse Books</a>
        </div>
    </div>

<?php else: ?>

    <div class="cart-wrapper">
        <div class="cart-container">
            <!-- Items List -->
            <div class="cart-items-section">
                <h2 style="margin-bottom:1.5rem;">Items in Cart (<?= count($cartItems) ?>)</h2>
                
                <div class="cart-list" id="cartList">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" id="cart-item-<?= $item['id_cart'] ?>">
                            <!-- Book info loaded by JavaScript -->
                            <div id="book-<?= $item['book_id'] ?>" class="cart-item__book">
                                <div class="skeleton" style="width:80px;aspect-ratio:2/3;border-radius:8px;flex-shrink:0;"></div>
                                <div style="flex:1;">
                                    <div class="skeleton" style="height:14px;width:140px;margin-bottom:.5rem;"></div>
                                    <div class="skeleton" style="height:12px;width:100px;margin-bottom:.4rem;"></div>
                                    <div class="skeleton" style="height:11px;width:80px;"></div>
                                </div>
                            </div>

                            <!-- Type & Details -->
                            <div class="cart-item__meta">
                                <span class="badge <?= $item['type'] === 'buy' ? 'badge-confirmed' : 'badge-active' ?>">
                                    <?= $item['type'] === 'buy' ? '🛍️ Buy' : '📖 Rent' ?>
                                </span>
                                <?php if ($item['type'] === 'rental'): ?>
                                    <p class="rental-info">
                                        <strong><?= (int)$item['rental_months'] ?></strong> month<?= (int)$item['rental_months'] !== 1 ? 's' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Price -->
                            <div class="cart-item__price">
                                <p class="price-display">
                                    <?php 
                                    $price = $item['type'] === 'buy' ? 50 : (10 * (int)$item['rental_months']);
                                    echo number_format($price, 2, '.', '') . ' MAD';
                                    ?>
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <button class="btn btn-danger btn-sm" 
                                    onclick="removeItem(<?= $item['id_cart'] ?>)"
                                    title="Remove from cart">
                                🗑️ Remove
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Summary Section -->
            <aside class="cart-summary">
                <h3>Order Summary</h3>
                
                <?php
                $buyCount = 0;
                $rentalCount = 0;
                $totalPrice = 0;
                
                foreach ($cartItems as $item) {
                    if ($item['type'] === 'buy') {
                        $buyCount++;
                        $totalPrice += 50;
                    } else {
                        $rentalCount++;
                        $totalPrice += 10 * (int)$item['rental_months'];
                    }
                }
                ?>
                
                <div class="summary-row">
                    <span>Buy Orders:</span>
                    <strong><?= $buyCount ?></strong>
                </div>
                
                <div class="summary-row">
                    <span>Rental Orders:</span>
                    <strong><?= $rentalCount ?></strong>
                </div>
                
                <div class="summary-row">
                    <span>Total Items:</span>
                    <strong><?= count($cartItems) ?></strong>
                </div>

                <div style="border-top:1px solid var(--border);margin:1rem 0;"></div>

                <div class="summary-total">
                    <span>Estimated Total:</span>
                    <span class="total-price">
                        <?= number_format($totalPrice, 2, '.', '') ?> MAD
                    </span>
                </div>

                <p class="note-text">💡 Final total may vary based on shipping and additional options during checkout.</p>

                <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:1.5rem;">
                    <a href="checkout.php" class="btn btn-primary btn-block">
                        Continue to Checkout →
                    </a>
                    <a href="catalogue.php" class="btn btn-secondary btn-block">
                        Continue Shopping
                    </a>
                </div>
            </aside>
        </div>
    </div>

<?php endif; ?>

<script src="../assets/js/cart.js"></script>

</body>
</html>
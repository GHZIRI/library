<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get & validate book id from URL
$book_id = sanitize($_GET['id'] ?? '');

if (empty($book_id)) {
    redirect('catalogue.php');
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Detail — Library</title>
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

<!-- Alert container -->
<div id="alertBox" style="max-width:900px;margin:1rem auto;padding:0 1.5rem;"></div>

<!-- Book detail loaded by JS -->
<div id="bookDetail">
    <!-- Skeleton while loading -->
    <div class="book-detail-layout">
        <div class="skeleton" style="width:100%;aspect-ratio:2/3;border-radius:12px;"></div>
        <div>
            <div class="skeleton" style="height:28px;width:70%;margin-bottom:1rem;"></div>
            <div class="skeleton" style="height:14px;width:40%;margin-bottom:.75rem;"></div>
            <div class="skeleton" style="height:14px;width:100%;margin-bottom:.4rem;"></div>
            <div class="skeleton" style="height:14px;width:90%;margin-bottom:.4rem;"></div>
            <div class="skeleton" style="height:14px;width:80%;"></div>
        </div>
    </div>
</div>

<script>
    const book_id = "<?= $book_id ?>";
    const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

    fetch(`https://www.googleapis.com/books/v1/volumes/${book_id}?key=${API_KEY}`)
        .then(res => res.json())
        .then(book => {
            const info        = book.volumeInfo;
            const title       = info.title       || 'No title';
            const author      = info.authors     ? info.authors[0] : 'Unknown';
            const cover       = info.imageLinks  ? info.imageLinks.thumbnail : '';
            const description = info.description || 'No description available.';
            const buy_price   = 50;
            const rent_price  = 10;

            document.getElementById('bookDetail').innerHTML = `
                <div class="book-detail-layout">
                    <img src="${cover}" alt="${title}" onerror="this.style.display='none'">
                    <div>
                        <h1 class="book-detail__title">${title}</h1>
                        <p class="book-detail__meta">✍️ ${author}</p>
                        <p class="book-detail__desc">${description}</p>
                        <div class="price-tags">
                            <div class="price-tag">Buy — <span>${buy_price} DH</span></div>
                            <div class="price-tag">Rent — <span>${rent_price} DH/month</span></div>
                        </div>
                        <div class="book-actions">
                            <button class="btn btn-primary" onclick="addToCart('${book.id}', 'buy')">
                                🛍️ Buy
                            </button>
                            <button class="btn btn-secondary" onclick="addToCart('${book.id}', 'rental')">
                                📖 Rent
                            </button>
                            <a href="catalogue.php" class="btn btn-secondary">← Back</a>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(() => {
            document.getElementById('bookDetail').innerHTML =
                '<p class="empty-state">Failed to load book details.</p>';
        });

    function addToCart(book_id, type) {
        fetch('../api/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ book_id, type })
        })
        .then(res => res.json())
        .then(data => {
            const alertBox = document.getElementById('alertBox');
            if (data.success) {
                alertBox.innerHTML =
                    '<div class="alert alert-success">✅ Added to cart successfully! <a href="cart.php" style="margin-left:auto;">View Cart</a></div>';
            } else {
                alertBox.innerHTML =
                    `<div class="alert alert-error">⚠️ ${data.message || 'Failed to add to cart'}</div>`;
            }
            setTimeout(() => alertBox.innerHTML = '', 4000);
        })
        .catch(err => {
            console.error('Error:', err);
            document.getElementById('alertBox').innerHTML =
                '<div class="alert alert-error">⚠️ Network error. Please try again.</div>';
        });
    }
</script>

</body>
</html>
<?php
require_once '../core/functions.php';

// No login required — allow free browsing
// Get & validate book id from URL
$book_id = sanitize($_GET['id'] ?? '');

if (empty($book_id)) {
    redirect('catalogue.php');
}

$user = currentUser(); // May be null if not logged in
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
            <?php if (isLoggedIn()): ?>
                <a href="cart.php">🛒 Cart</a>
                <a href="orders_history.php">📋 My Orders</a>
                <a href="user_dashboard.php">👤 <?= sanitize($user['name_user']) ?></a>
                <a href="../core/logout.php" class="btn-nav-logout">🚪 Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">🔑 Sign In</a>
                <a href="register.php">📝 Register</a>
            <?php endif; ?>
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

            const detailDiv = document.getElementById('bookDetail');
            detailDiv.innerHTML = '';

            const layout = document.createElement('div');
            layout.className = 'book-detail-layout';

            // Image
            if (cover) {
                const img = document.createElement('img');
                img.src = cover;
                img.alt = title;
                img.onerror = () => { img.style.display = 'none'; };
                layout.appendChild(img);
            }

            // Content container
            const contentDiv = document.createElement('div');

            // Title
            const titleEl = document.createElement('h1');
            titleEl.className = 'book-detail__title';
            titleEl.textContent = title;
            contentDiv.appendChild(titleEl);

            // Author
            const authorEl = document.createElement('p');
            authorEl.className = 'book-detail__meta';
            authorEl.textContent = '✍️ ' + author;
            contentDiv.appendChild(authorEl);

            // Description
            const descEl = document.createElement('p');
            descEl.className = 'book-detail__desc';
            descEl.textContent = description;
            contentDiv.appendChild(descEl);

            // Price tags
            const priceTagsDiv = document.createElement('div');
            priceTagsDiv.className = 'price-tags';

            const buyPriceTag = document.createElement('div');
            buyPriceTag.className = 'price-tag';
            buyPriceTag.innerHTML = 'Buy — <span>' + buy_price + ' DH</span>';
            priceTagsDiv.appendChild(buyPriceTag);

            const rentPriceTag = document.createElement('div');
            rentPriceTag.className = 'price-tag';
            rentPriceTag.innerHTML = 'Rent — <span>' + rent_price + ' DH/month</span>';
            priceTagsDiv.appendChild(rentPriceTag);

            contentDiv.appendChild(priceTagsDiv);

            // Actions
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'book-actions';

            const buyBtn = document.createElement('button');
            buyBtn.className = 'btn btn-primary';
            buyBtn.textContent = '🛍️ Buy';
            buyBtn.onclick = () => addToCart(book.id, 'buy');
            actionsDiv.appendChild(buyBtn);

            const rentBtn = document.createElement('button');
            rentBtn.className = 'btn btn-secondary';
            rentBtn.textContent = '📖 Rent';
            rentBtn.onclick = () => addToCart(book.id, 'rental');
            actionsDiv.appendChild(rentBtn);

            const backLink = document.createElement('a');
            backLink.href = 'catalogue.php';
            backLink.className = 'btn btn-secondary';
            backLink.textContent = '← Back';
            actionsDiv.appendChild(backLink);

            contentDiv.appendChild(actionsDiv);
            layout.appendChild(contentDiv);
            detailDiv.appendChild(layout);
        })
        .catch(() => {
            const detailDiv = document.getElementById('bookDetail');
            detailDiv.innerHTML = '';
            const errorEl = document.createElement('p');
            errorEl.className = 'empty-state';
            errorEl.textContent = 'Failed to load book details.';
            detailDiv.appendChild(errorEl);
        });

    function addToCart(book_id, type) {
        fetch('../api/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ book_id, type })
        })
        .then(res => {
            if (res.status === 401) {
                // User not logged in
                alert('⚠️ Please sign in to add books to your cart');
                window.location.href = 'login.php';
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return;
            
            const alertBox = document.getElementById('alertBox');
            if (data.success) {
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success';
                successMsg.textContent = '✅ Added to cart successfully! ';
                const link = document.createElement('a');
                link.href = 'cart.php';
                link.textContent = 'View Cart';
                link.style.marginLeft = 'auto';
                successMsg.appendChild(link);
                alertBox.innerHTML = '';
                alertBox.appendChild(successMsg);
            } else {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-error';
                errorMsg.textContent = '⚠️ ' + (data.message || 'Failed to add to cart');
                alertBox.innerHTML = '';
                alertBox.appendChild(errorMsg);
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
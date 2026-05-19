<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user    = currentUser();
$user_id = $user['id_user'];

// Get cart items
$cartItems = getCart($user_id);

// If cart is empty, redirect to catalogue
if (empty($cartItems)) {
    redirect('catalogue.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar__inner">
        <a href="catalogue.php" class="navbar__brand">📚 <span>Library</span></a>
        <div class="navbar__links">
            <a href="cart.php">← Back to Cart</a>
            <a href="../core/logout.php" class="btn-nav-logout">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="page-header">
    <h1>Checkout</h1>
    <p>Fill in your details to complete the order</p>
</div>

<form action="payment.php" method="POST" onsubmit="return validateCheckoutForm()">
    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
    <div class="checkout-layout">

        <!-- Left: Personal info + order type -->
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

            <div class="checkout-section">
                <h3>📋 Your Information</h3>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city"
                           placeholder="Your city" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number (Moroccan)</label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="06XXXXXXXX" pattern="^06[0-9]{8}$" required>
                    <small style="color:var(--text-muted);display:block;margin-top:.25rem;">
                        Format: 06XXXXXXXX (10 digits)
                    </small>
                </div>
            </div>

            <div class="checkout-section">
                <h3>🛍️ Order Type</h3>
                <div class="form-group">
                    <label for="type">Select Order Type</label>
                    <select id="type" name="type" required>
                        <option value="buy">🛍️ Buy a Book</option>
                        <option value="rental">📖 Rent a Book</option>
                    </select>
                </div>

                <!-- Rental months — only shows if rental selected -->
                <div id="rentalMonths" class="form-group" style="display:none;">
                    <label for="rental_months">Number of Months</label>
                    <input type="number" id="rental_months" name="rental_months"
                           placeholder="e.g. 3" min="1" max="12">
                    <small style="color:var(--text-muted);display:block;margin-top:.25rem;">
                        📅 You can rent for 1-12 months
                    </small>
                </div>
            </div>

        </div>

        <!-- Right: Books summary + confirm button -->
        <div class="checkout-section">
            <h3>📚 Order Summary</h3>

            <div id="cartBooks" style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.5rem;max-height:400px;overflow-y:auto;">
                <?php foreach ($cartItems as $item): ?>
                    <div id="book-<?= $item['book_id'] ?>" class="checkout-book-item"
                         style="display:flex;align-items:center;gap:.75rem;padding:.65rem;background:var(--bg-hover);border-radius:6px;">
                        <div class="skeleton" style="width:50px;aspect-ratio:2/3;border-radius:6px;flex-shrink:0;"></div>
                        <div style="flex:1;">
                            <div class="skeleton" style="height:13px;width:110px;margin-bottom:.35rem;"></div>
                            <div class="skeleton" style="height:11px;width:70px;"></div>
                        </div>
                    </div>
                    <!-- Pass book_id as hidden input -->
                    <input type="hidden" name="book_ids[]" value="<?= $item['book_id'] ?>">
                <?php endforeach; ?>
            </div>

            <!-- Summary Stats -->
            <div style="background:var(--bg-hover);border-radius:8px;padding:1rem;margin-bottom:1rem;font-size:.9rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                    <span>Items:</span>
                    <strong><?= count($cartItems) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding-top:.5rem;border-top:1px solid var(--border);">
                    <span>Estimated Total:</span>
                    <strong id="totalPrice" style="color:var(--accent-light);">
                        <?php 
                        $total = 0;
                        foreach ($cartItems as $item) {
                            if ($item['type'] === 'buy') {
                                $total += 50;
                            } else {
                                $total += 10 * (int)$item['rental_months'];
                            }
                        }
                        echo number_format($total, 2, '.', '') . ' MAD';
                        ?>
                    </strong>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:0.75rem;">
                ✅ Confirm & Proceed to Payment
            </button>

            <a href="cart.php" class="btn btn-secondary" style="width:100%;margin-top:.5rem;text-align:center;">
                ← Back to Cart
            </a>
        </div>

    </div>
</form>

<script src="../assets/js/payment.js"></script>

<script>
    // ── Initialize Checkout Page ────────────────────────────────────────────
    const API_KEY = "YOUR_GOOGLE_BOOKS_API_KEY";

    document.addEventListener('DOMContentLoaded', () => {
        loadCheckoutBooks();
        setupRentalToggle();
    });

    // ── Load Book Details ───────────────────────────────────────────────────
    const loadCheckoutBooks = () => {
        document.querySelectorAll('[id^="book-"]').forEach(div => {
            const bookId = div.id.replace('book-', '');
            fetchCheckoutBook(bookId, div);
        });
    };

    // ── Fetch Book Details ──────────────────────────────────────────────────
    const fetchCheckoutBook = async (bookId, containerElement) => {
        try {
            const url = `https://www.googleapis.com/books/v1/volumes/${bookId}?key=${API_KEY}`;
            const response = await fetch(url);
            
            if (!response.ok) throw new Error('API error');
            
            const book = await response.json();
            const info = book.volumeInfo;
            const title = info.title || 'No title';
            const cover = info.imageLinks?.thumbnail || '';

            containerElement.innerHTML = `
                ${cover ? `<img src="${cover}" alt="${title}" style="width:50px;aspect-ratio:2/3;object-fit:cover;border-radius:6px;flex-shrink:0;">` : ''}
                <p style="font-size:.85rem;font-weight:600;flex:1;">${title}</p>
            `;
        } catch (error) {
            console.error("Error fetching book:", error);
            containerElement.innerHTML = '<p style="font-size:.82rem;color:var(--text-muted);">Book details unavailable</p>';
        }
    };

    // ── Setup Rental Toggle ─────────────────────────────────────────────────
    const setupRentalToggle = () => {
        const typeSelect = document.getElementById('type');
        const rentalDiv = document.getElementById('rentalMonths');

        if (typeSelect) {
            typeSelect.addEventListener('change', () => {
                if (typeSelect.value === 'rental') {
                    rentalDiv.style.display = 'block';
                    document.getElementById('rental_months').required = true;
                } else {
                    rentalDiv.style.display = 'none';
                    document.getElementById('rental_months').required = false;
                }
                updateCheckoutTotal();
            });
        }
    };

    // ── Validate Checkout Form ──────────────────────────────────────────────
    const validateCheckoutForm = () => {
        const name = document.getElementById('name').value.trim();
        const city = document.getElementById('city').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const type = document.getElementById('type').value;
        const rentalMonths = document.getElementById('rental_months');

        // Check required fields
        if (!name || !city || !phone) {
            showCheckoutAlert('Please fill all required fields', 'error');
            return false;
        }

        // Validate phone format
        if (!/^06[0-9]{8}$/.test(phone)) {
            showCheckoutAlert('Invalid phone number. Use format: 06XXXXXXXX', 'error');
            return false;
        }

        // Validate rental months
        if (type === 'rental') {
            const months = parseInt(rentalMonths.value);
            if (!months || months < 1 || months > 12) {
                showCheckoutAlert('Please enter rental months (1-12)', 'error');
                return false;
            }
        }

        return true;
    };

    // ── Update Checkout Total ───────────────────────────────────────────────
    const updateCheckoutTotal = () => {
        const bookCount = document.querySelectorAll('[id^="book-"]').length;
        const type = document.getElementById('type').value;
        const rentalMonths = parseInt(document.getElementById('rental_months')?.value || 1);

        const unitPrice = type === 'buy' ? 50 : 10;
        const multiplier = type === 'buy' ? 1 : rentalMonths;
        const total = bookCount * unitPrice * multiplier;

        const totalElement = document.getElementById('totalPrice');
        if (totalElement) {
            totalElement.textContent = number_format(total, 2) + ' MAD';
        }
    };

    // ── Utility: Format Number ──────────────────────────────────────────────
    const number_format = (num, decimals) => {
        return num.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    // ── Show Alert ──────────────────────────────────────────────────────────
    const showCheckoutAlert = (message, type = 'info') => {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const target = document.querySelector('.checkout-layout');
        if (target) {
            target.insertAdjacentElement('beforebegin', alertDiv);
            setTimeout(() => alertDiv.remove(), 4000);
        }
    };
</script>

</body>
</html>
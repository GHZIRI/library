<?php
/**
 * Book Rental Page
 * 
 * Form to rent a book (requires login)
 */

require_once '../core/functions.php';

// Require login
requireLogin();

// Verify book ID is provided
if (empty($_GET['book_id'])) {
    redirect('catalogue.php');
}

$book_id = sanitize($_GET['book_id']);
$book = getBookById($book_id);
$user_id = getCurrentUserId();

// If book not found or not available for rent
if (!$book || !$book['available_rental']) {
    redirect('catalogue.php');
}

// Process rental (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security error. Please try again.');
        redirect("rent.php?book_id={$book_id}");
    }

    // Verify fields
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $rental_days = intval($_POST['rental_days'] ?? 7);
    $card_number = sanitize($_POST['card_number'] ?? '');

    if (empty($name) || empty($phone) || empty($city) || $rental_days < 1 || empty($card_number)) {
        setFlash('error', 'Please fill in all required fields correctly.');
        redirect("rent.php?book_id={$book_id}");
    }

    // Basic credit card validation (must be at least 4 digits)
    if (strlen($card_number) < 4) {
        setFlash('error', 'Card number is invalid.');
        redirect("rent.php?book_id={$book_id}");
    }

    $card_last_four = substr($card_number, -4);

    // Create the order
    $total_price = $book['price_rental'] * $rental_days;
    $end_date = calculateEndDate($rental_days);

    $order_data = [
        'user_id' => $user_id,
        'book_id' => $book_id,
        'name' => $name,
        'phone' => $phone,
        'city' => $city,
        'rental_days' => $rental_days,
        'total_price' => $total_price,
        'card_last_four' => $card_last_four,
        'end_date' => $end_date
    ];

    if (createRentalOrder($order_data)) {
        setFlash('success', '✅ Rental order received! Enjoy reading your book.');
        redirect('order_confirmation.php');
    } else {
        setFlash('error', 'An error occurred. Please try again later.');
        redirect("rent.php?book_id={$book_id}");
    }
}

$success = getFlash('success');
$error = getFlash('error');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent - <?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .navbar-links {
            display: flex;
            gap: 20px;
            list-style: none;
            flex-direction: row;
        }
        .form-group label {
            text-align: left;
            display: block;
        }
    </style>
    <script>
        // Real-time rental price calculator
        function updatePrice() {
            var days = document.getElementById('rental_days').value;
            var pricePerDay = <?php echo floatval($book['price_rental']); ?>;
            var total = days * pricePerDay;
            document.getElementById('display_days').innerText = days + " day" + (days > 1 ? "s" : "");
            document.getElementById('display_total').innerText = total.toFixed(2) + " MAD";
        }
    </script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 Library</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">Home</a></li>
                <li><a href="user_dashboard.php">My Dashboard</a></li>
                <li><a href="../core/logout.php">Logout</a></li>
                <li><a href="../admin/login.php">Admin Panel</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 40px 0;">
            
            <!-- Book Details Column -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; border-bottom: 2px solid var(--light); padding-bottom: 10px;">📖 Book Details</h2>

                <!-- Cover Image -->
                <div style="width: 100%; height: 300px; background-color: var(--light); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 80px; margin-bottom: 20px;">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                    <?php else: ?>
                        📖
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p style="color: var(--gray); margin-bottom: 15px;">✍️ By <?php echo htmlspecialchars($book['author']); ?></p>
                <p style="margin-bottom: 15px;"><strong>Genre:</strong> <?php echo htmlspecialchars($book['type_name']); ?></p>
                <p style="margin-bottom: 15px;"><strong>Rental Price (Per Day):</strong> <span style="color: var(--secondary); font-size: 20px; font-weight: 700;"><?php echo formatPrice($book['price_rental']); ?></span></p>
                <?php if (!empty($book['description'])): ?>
                    <p style="color: var(--gray); line-height: 1.8; margin-top: 15px;"><?php echo htmlspecialchars($book['description']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Rental Form Column -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: fit-content;">
                <h2 style="margin-bottom: 20px; border-bottom: 2px solid var(--light); padding-bottom: 10px;">🔄 Rent Book</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                    <!-- Full Name -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Full Name *</label>
                        <input type="text" name="name" placeholder="Enter your full name" required maxlength="100" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" placeholder="e.g. 0612345678" required maxlength="20" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
                    </div>

                    <!-- City -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>City *</label>
                        <input type="text" name="city" placeholder="Enter your city" required maxlength="100" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
                    </div>

                    <!-- Rental Days -->
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Rental Period (Days) *</label>
                        <input type="number" id="rental_days" name="rental_days" value="7" min="1" max="30" required onchange="updatePrice()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
                    </div>

                    <!-- Credit Card -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Credit Card Number *</label>
                        <input type="text" name="card_number" placeholder="Enter credit card number" required maxlength="16" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
                    </div>

                    <!-- Pricing Summary -->
                    <div style="background-color: var(--light); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <p style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Price per Day:</span>
                            <span><?php echo formatPrice($book['price_rental']); ?></span>
                        </p>
                        <p style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Rental Duration:</span>
                            <span id="display_days">7 days</span>
                        </p>
                        <hr style="border: none; border-top: 1px solid var(--border); margin-bottom: 10px;">
                        <p style="display: flex; justify-content: space-between; font-weight: 700; font-size: 18px;">
                            <span>Total Price:</span>
                            <span id="display_total" style="color: var(--secondary);"><?php echo formatPrice($book['price_rental'] * 7); ?></span>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success" style="flex: 1; padding: 12px 0; border: none; font-weight: 600;">✅ Confirm Rent</button>
                        <a href="catalogue.php" class="btn btn-secondary" style="flex: 1; padding: 12px 0; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 600;">❌ Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Library. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

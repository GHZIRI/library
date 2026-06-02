<?php
/**
 * Buy Book Page
 * 
 * Form to purchase a book without needing to log in.
 */

require_once '../core/functions.php';

// Verify book ID is provided
if (empty($_GET['book_id'])) {
    redirect('catalogue.php');
}

$book_id = sanitize($_GET['book_id']);
$book = getBookById($book_id);

// If book not found or not available for buying
if (!$book || !$book['available_buy']) {
    redirect('catalogue.php');
}

// Process purchase (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security error. Please try again.');
        redirect("buy.php?book_id={$book_id}");
    }

    // Verify fields
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $quantity = 1; // Default to 1 as the counter was removed

    if (empty($name) || empty($phone) || empty($city)) {
        setFlash('error', 'Please fill in all required fields correctly.');
        redirect("buy.php?book_id={$book_id}");
    }

    // Create the purchase order
    $total_price = $book['price_buy'] * $quantity;
    $order_data = [
        'user_id' => getCurrentUserId(),
        'book_id' => $book_id,
        'name' => $name,
        'phone' => $phone,
        'city' => $city,
        'quantity' => $quantity,
        'total_price' => $total_price
    ];

    if (createBuyOrder($order_data)) {
        setFlash('success', '✅ Purchase order received! We will contact you soon.');
        redirect('order_confirmation.php');
    } else {
        setFlash('error', 'An error occurred. Please try again later.');
        redirect("buy.php?book_id={$book_id}");
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
    <title>Buy - <?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Adjust navbar and links to support LTR perfectly */
        .navbar-links {
            flex-direction: row-reverse;
        }
        .form-group label {
            text-align: left;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 Library</a>
            <ul class="navbar-links" style="display: flex; gap: 20px; list-style: none; flex-direction: row;">
                <li><a href="catalogue.php">Home</a></li>
                <li><a href="../admin/login.php">Admin Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 40px 0;">
            
            <!-- Book Details Column -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; border-bottom: 2px solid var(--light); padding-bottom: 10px;">📖 Book Details</h2>

                <!-- Book Cover -->
                <div style="width: 100%; height: 300px; background-color: var(--light); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 80px; margin-bottom: 20px;">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                    <?php else: ?>
                        📖
                    <?php endif; ?>
                </div>

                <!-- Book Information -->
                <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p style="color: var(--gray); margin-bottom: 15px;">✍️ By <?php echo htmlspecialchars($book['author']); ?></p>
                <p style="margin-bottom: 15px;"><strong>Genre:</strong> <?php echo htmlspecialchars($book['type_name']); ?></p>
                <p style="margin-bottom: 15px;"><strong>Price:</strong> <span style="color: var(--secondary); font-size: 20px; font-weight: 700;"><?php echo formatPrice($book['price_buy']); ?></span></p>
                <?php if (!empty($book['description'])): ?>
                    <p style="color: var(--gray); line-height: 1.8; margin-top: 15px;"><?php echo htmlspecialchars($book['description']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Checkout Form Column -->
            <div style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: fit-content;">
                <h2 style="margin-bottom: 20px; border-bottom: 2px solid var(--light); padding-bottom: 10px;">🛒 Checkout</h2>

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
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" placeholder="Enter your full name" required maxlength="100">
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" placeholder="e.g. 0612345678" required maxlength="20">
                    </div>

                    <!-- City -->
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" name="city" placeholder="Enter your city" required maxlength="100">
                    </div>

                    <!-- Price Summary (Quantity field removed as requested) -->
                    <div style="background-color: var(--light); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <p style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Book Price:</span>
                            <span><?php echo formatPrice($book['price_buy']); ?></span>
                        </p>
                        <hr style="border: none; border-top: 1px solid var(--border); margin-bottom: 10px;">
                        <p style="display: flex; justify-content: space-between; font-weight: 700; font-size: 18px;">
                            <span>Total Price:</span>
                            <span style="color: var(--secondary);"><?php echo formatPrice($book['price_buy']); ?></span>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success" style="flex: 1;">✅ Confirm Purchase</button>
                        <a href="catalogue.php" class="btn btn-secondary" style="flex: 1;">❌ Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Library. All rights reserved.</p>
    </footer>
</body>
</html>

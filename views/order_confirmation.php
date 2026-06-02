<?php
/**
 * Order Confirmation Page
 * 
 * Displayed after a successful purchase or rental transaction.
 */

require_once '../core/functions.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .navbar-links {
            display: flex;
            gap: 20px;
            list-style: none;
            flex-direction: row;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 Library</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">Home</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="user_dashboard.php">My Dashboard</a></li>
                    <li><a href="../core/logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container">
        <div style="max-width: 600px; margin: 60px auto; background-color: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <!-- Icon -->
            <p style="font-size: 80px; margin-bottom: 20px;">✅</p>

            <!-- Main Message -->
            <h1 style="color: var(--success); font-size: 28px; margin-bottom: 15px; font-weight: 700;">Order Confirmed!</h1>

            <!-- Description -->
            <p style="color: var(--gray); font-size: 16px; line-height: 1.8; margin-bottom: 30px;">
                Thank you for using our services! Your order has been successfully received and is currently being processed. We will contact you shortly.
            </p>

            <!-- Info Summary Box -->
            <div style="background-color: var(--light); padding: 20px; border-radius: 5px; margin-bottom: 30px; text-align: left;">
                <p style="color: var(--gray); margin-bottom: 10px; font-size: 15px;">📋 <strong>Order ID:</strong> #<?php echo date('YmdHis'); ?></p>
                <p style="color: var(--gray); margin-bottom: 10px; font-size: 15px;">📅 <strong>Date:</strong> <?php echo formatDate(date('Y-m-d H:i:s')); ?></p>
                <p style="color: var(--gray); font-size: 15px;">⏳ <strong>Status:</strong> <span style="background: rgba(243, 156, 18, 0.1); color: #d35400; padding: 2px 8px; border-radius: 15px; font-size: 13px; font-weight: 600;">Processing</span></p>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="catalogue.php" class="btn btn-primary" style="padding: 10px 25px; text-decoration: none; font-weight: 600;">🏠 Return Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="user_dashboard.php" class="btn btn-secondary" style="padding: 10px 25px; text-decoration: none; font-weight: 600;">👤 My Dashboard</a>
                <?php endif; ?>
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

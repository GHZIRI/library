<?php
/**
 * User Dashboard
 * 
 * Interactive overview of purchases and rentals for the current logged-in user.
 */

require_once '../core/functions.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$user = getUserById($user_id);
$user_name = $user['name_user'] ?? 'User';

// Retrieve purchase and rental orders
$buy_orders = getUserBuyOrders($user_id);
$rental_orders = getUserRentalOrders($user_id);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .navbar-links {
            display: flex;
            gap: 20px;
            list-style: none;
            flex-direction: row;
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), #5a52d5);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin: 30px 0 15px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 10px;
            text-align: left;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .badge-pending { background-color: #fef3c7; color: #d97706; }
        .badge-confirmed { background-color: #d1fae5; color: #059669; }
        .badge-shipped { background-color: #dbeafe; color: #2563eb; }
        .badge-delivered { background-color: #d1fae5; color: #059669; }
        .badge-cancelled { background-color: #fee2e2; color: #dc2626; }
        .badge-active { background-color: #e0f2fe; color: #0284c7; }
        .badge-returned { background-color: #f3f4f6; color: #4b5563; }
        
        .orders-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 35px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background-color: var(--light);
            font-weight: 700;
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
                <li><a href="user_dashboard.php">My Dashboard</a></li>
                <li><a href="../core/logout.php" class="btn btn-danger" style="color: white; padding: 6px 12px; font-size: 14px; text-decoration: none; border-radius: 5px;">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container">
        
        <!-- Welcome Header -->
        <div class="dashboard-header" style="text-align: left;">
            <h1 style="font-size: 28px; margin-bottom: 5px; font-weight: 700;">👤 Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p style="opacity: 0.9;">Track and manage your purchases, rented books, and transaction history.</p>
        </div>

        <!-- Quick Navigation Tabs -->
        <ul style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid var(--border); padding-bottom: 10px; list-style: none; padding-left: 0; align-items: center; width: 100%;">
            <li><a href="#buy-orders" style="color: var(--primary); text-decoration: none; font-weight: 600; padding: 10px 20px; border-bottom: 3px solid var(--primary); display: inline-block;">🛒 My Purchases</a></li>
            <li><a href="#rental-orders" style="color: var(--gray); text-decoration: none; font-weight: 600; padding: 10px 20px; display: inline-block; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--gray)'">🔄 My Rentals</a></li>
            <li style="margin-left: auto;"><a href="catalogue.php" class="btn btn-primary" style="padding: 8px 16px; text-decoration: none;">📚 Browse Catalogue</a></li>
        </ul>

        <!-- Purchases Section -->
        <div id="buy-orders" class="orders-container">
            <h2 class="section-title" style="margin-top: 0;">🛒 Purchased Books</h2>
            
            <?php if (count($buy_orders) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Book Title</th>
                                <th style="text-align: center;">Quantity</th>
                                <th>Total Price</th>
                                <th>Purchase Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($buy_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['order_buy_id']; ?></strong></td>
                                    <td style="font-weight: 600;">📖 <?php echo htmlspecialchars($order['title']); ?></td>
                                    <td style="text-align: center; font-weight: 700;"><?php echo $order['quantity']; ?></td>
                                    <td style="color: var(--secondary); font-weight: 700;"><?php echo formatPrice($order['total_price']); ?></td>
                                    <td><?php echo formatDate($order['created_at']); ?></td>
                                    <td>
                                        <span class="status-badge badge-<?php echo $order['status']; ?>">
                                            <?php 
                                            $statuses = [
                                                'pending' => '⏳ Pending',
                                                'confirmed' => '✅ Confirmed',
                                                'shipped' => '🚚 Shipped',
                                                'delivered' => '📦 Delivered',
                                                'cancelled' => '❌ Cancelled'
                                            ];
                                            echo $statuses[$order['status']] ?? ucfirst($order['status']);
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background-color: var(--light); border-radius: 10px;">
                    <p style="font-size: 50px; margin-bottom: 15px;">📭</p>
                    <p style="color: var(--gray); font-size: 16px;">You have not purchased any books yet.</p>
                    <a href="catalogue.php" class="btn btn-primary" style="margin-top: 15px; display: inline-block; text-decoration: none;">🛒 Browse & Buy Now</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rentals Section -->
        <div id="rental-orders" class="orders-container">
            <h2 class="section-title" style="margin-top: 0;">🔄 Rented Books</h2>
            
            <?php if (count($rental_orders) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Rental ID</th>
                                <th>Book Title</th>
                                <th style="text-align: center;">Duration</th>
                                <th>Total Price</th>
                                <th>Start Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rental_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['order_rental_id']; ?></strong></td>
                                    <td style="font-weight: 600;">📖 <?php echo htmlspecialchars($order['title']); ?></td>
                                    <td style="text-align: center; font-weight: 700;"><?php echo $order['rental_days']; ?> days</td>
                                    <td style="color: var(--secondary); font-weight: 700;"><?php echo formatPrice($order['total_price']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($order['start_date'])); ?></td>
                                    <td style="font-weight: 600; color: var(--danger);"><?php echo date('Y-m-d', strtotime($order['end_date'])); ?></td>
                                    <td>
                                        <span class="status-badge badge-<?php echo $order['status']; ?>">
                                            <?php 
                                            $statuses = [
                                                'pending' => '⏳ Pending',
                                                'active' => '🔄 Active',
                                                'returned' => '✅ Returned',
                                                'cancelled' => '❌ Cancelled'
                                            ];
                                            echo $statuses[$order['status']] ?? ucfirst($order['status']);
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background-color: var(--light); border-radius: 10px;">
                    <p style="font-size: 50px; margin-bottom: 15px;">📭</p>
                    <p style="color: var(--gray); font-size: 16px;">You have not rented any books yet.</p>
                    <a href="catalogue.php" class="btn btn-secondary" style="margin-top: 15px; display: inline-block; text-decoration: none;">📖 Browse & Rent Now</a>
                </div>
            <?php endif; ?>
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

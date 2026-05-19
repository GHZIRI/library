<?php
require_once '../core/functions.php';

// If not logged in → login
if (!isLoggedIn()) {
    redirect('../views/login.php');
}

// If not admin → catalogue
if (!isAdmin()) {
    redirect('../views/catalogue.php');
}

// Get all buy orders
$buy_orders = $pdo->query("SELECT orders_buy.*, users.name_user, users.email 
    FROM orders_buy 
    JOIN users ON orders_buy.id_user = users.id_user 
    ORDER BY orders_buy.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get all rental orders
$rental_orders = $pdo->query("SELECT orders_rental.*, users.name_user, users.email 
    FROM orders_rental 
    JOIN users ON orders_rental.id_user = users.id_user 
    ORDER BY orders_rental.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders — Library</title>
</head>
<body>

    <h1>Manage Orders</h1>
    <a href="dashboard.php">← Back to Dashboard</a>

    <!-- Buy Orders -->
    <h2>📚 Buy Orders</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Book ID</th>
            <th>Name</th>
            <th>City</th>
            <th>Phone</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

        <?php foreach ($buy_orders as $order) { ?>
        <tr>
            <td><?= $order['id_buy'] ?></td>
            <td><?= $order['name_user'] ?></td>
            <td><?= $order['book_id'] ?></td>
            <td><?= $order['name_buy'] ?></td>
            <td><?= $order['city'] ?></td>
            <td><?= $order['phone_number'] ?></td>
            <td><?= $order['total_price'] ?> DH</td>
            <td><?= $order['status'] ?></td>
            <td><?= $order['created_at'] ?></td>
            <td>
                <button onclick="updateStatus('buy', <?= $order['id_buy'] ?>, 'confirmed')">Confirm</button>
                <button onclick="updateStatus('buy', <?= $order['id_buy'] ?>, 'cancelled')">Cancel</button>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Rental Orders -->
    <h2>📖 Rental Orders</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Book ID</th>
            <th>Name</th>
            <th>City</th>
            <th>Phone</th>
            <th>Months</th>
            <th>Total</th>
            <th>Start</th>
            <th>End</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php foreach ($rental_orders as $order) { ?>
        <tr>
            <td><?= $order['id_rental'] ?></td>
            <td><?= $order['name_user'] ?></td>
            <td><?= $order['book_id'] ?></td>
            <td><?= $order['name_rental'] ?></td>
            <td><?= $order['city'] ?></td>
            <td><?= $order['phone_number'] ?></td>
            <td><?= $order['rental_months'] ?></td>
            <td><?= $order['total_price'] ?> DH</td>
            <td><?= $order['start_date'] ?></td>
            <td><?= $order['end_date'] ?></td>
            <td><?= $order['status'] ?></td>
            <td>
                <button onclick="updateStatus('rental', <?= $order['id_rental'] ?>, 'active')">Activate</button>
                <button onclick="updateStatus('rental', <?= $order['id_rental'] ?>, 'cancelled')">Cancel</button>
            </td>
        </tr>
        <?php } ?>
    </table>

    <script>
        // Update order status
        function updateStatus(type, id, status) {
            fetch('../api/process_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, id: id, status: status })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>

</body>
</html>
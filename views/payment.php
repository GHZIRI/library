<?php
require_once '../core/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('catalogue.php');
}

$user    = currentUser();
$user_id = $user['id_user'];

$errors = [];

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $errors[] = "Invalid security token. Please try again.";
}

if (empty($errors)) {
    // Get & sanitize form data
    $name          = sanitize($_POST['name']          ?? '');
    $city          = sanitize($_POST['city']          ?? '');
    $phone         = sanitize($_POST['phone']         ?? '');
    $type          = sanitize($_POST['type']          ?? '');
    $rental_months = isset($_POST['rental_months']) ? (int)$_POST['rental_months'] : 1;
    $book_ids      = $_POST['book_ids'] ?? [];

    // Basic validation
    if (empty($name) || empty($city) || empty($phone) || empty($book_ids)) {
        $errors[] = "All fields are required.";
    }

    if ($type === 'rental' && $rental_months < 1) {
        $errors[] = "Please enter a valid number of rental months.";
    }

    // Loop through each book and create order
    if (empty($errors)) {
        foreach ($book_ids as $book_id) {
            $book_id = sanitize($book_id);

            if ($type === 'buy') {
                $result = createBuyOrder($user_id, $book_id, $name, $city, $phone, 1, 50.00);
            } else {
                $start_date = date('Y-m-d');
                $end_date   = date('Y-m-d', strtotime("+{$rental_months} months"));
                $result     = createRentalOrder(
                    $user_id, $book_id, $name, $city, $phone,
                    $rental_months, 10.00 * $rental_months,
                    $start_date, $end_date
                );
            }

            if (!$result) {
                $errors[] = "Error processing book: " . htmlspecialchars($book_id, ENT_QUOTES, 'UTF-8');
            }
        }
    }

    // If no errors, clear cart and redirect
    if (empty($errors)) {
        clearCart($user_id);
        redirect('order_confirmation.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment — Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar__inner">
        <a href="catalogue.php" class="navbar__brand">📚 <span>Library</span></a>
    </div>
</nav>

<div class="page-header">
    <h1>Payment Error</h1>
    <p>Something went wrong while processing your order.</p>
</div>

<div style="max-width:700px;margin:0 auto;padding:0 1.5rem 2rem;">
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error">⚠️ <?= $error ?></div>
    <?php endforeach; ?>
    <a href="checkout.php" class="btn btn-secondary" style="margin-top:.5rem;">
        ← Go Back to Checkout
    </a>
</div>

</body>
</html>

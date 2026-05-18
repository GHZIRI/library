<?php

use function PHPSTORM_META\type;

require_once '../core/functions.php';


// If not logged in, redirect to login
if(!isLoggedIn()){
    redirect('login.php');
}

$user_id = currentUser()['id_user'];


// Get data from checkout form
$name = sanitize($_POST["name"]);
$city = sanitize($_POST["city"]);
$phone = sanitize($_POST["phone"]);
$type  = sanitize($_POST['type']);
$rental_months = isset($_POST['rental_months']) ? (int)$_POST['rental_months'] : null;
$book_ids = $_POST['book_ids'];

$errors = [];



// Loop through each book and create order
foreach($book_ids as $book_id){
    if($type === 'buy'){
        $result = createBuyOrder($user_id, $book_id, $name, $city, $phone, 1, 50);
    }else{
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$rental_months} months"));
        $result = createBuyOrder($user_id, $book_id, $name, $city, $phone, $rental_months, 10 * $rental_months, $start_date, $end_date);
    }
    if(!$result){
        $errors[] ="Error with book: $book_id";
    }
}



// If no errors, clear cart and redirect
if(empty($errors)){
    clearCart($user_id);
    redirect('order_confirmation.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment — Library</title>
</head>
<body>

    <h1>Payment</h1>

    <!-- Show errors if any -->
    <?php if (!empty($errors)) { ?>
        <?php foreach ($errors as $error) { ?>
            <p style="color:red"><?= $error ?></p>
        <?php } ?>
        <a href="checkout.php">Go back</a>
    <?php } ?>

</body>
</html>


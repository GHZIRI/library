<?php
require_once 'db.php';
 session_start();
 function isLoggedin(){
    return isset($_SESSION['user']);
 }

 function currentUser () {
    return $_SESSION['user']?? null;
 }
function isadmin(){
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}
function redirect($url) {
    header("Location: $url");
    exit();
}
function logout(){
    session_destroy();
    redirect('../views/login.php');
}
function sanitize($input){
    return htmlspecialchars(strip_tags(trim($input)));
}
function addToCart($user_id, $book_id, $type, $rent_days = null){
     global $pdo;
     $stmt = $pdo->prepare("SELECT * FROM cart WHERE id_user = ? AND book_id = ? AND type = ?");
     $stmt->execute([$user_id, $book_id, $type]);
     if($stmt->fetch()) return false;
     $stmt = $pdo->prepare("INSERT INTO cart (id_user, book_id, type, rent_days) VALUES (?, ?, ?, ?)");
     return $stmt->execute([$user_id, $book_id, $type, $rent_days]);
}
function getCart($user_id){
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE id_user = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function removeFromCart($id_cart, $user_id){
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id_cart = ? AND id_user = ?");
    return $stmt->execute([$id_cart, $user_id]);
}
function clearCart($user_id){
     global $pdo;
     $stmt = $pdo->prepare("DELETE FROM cart WHERE id_user = ?");
     return $stmt->execute([$user_id]);
}
function createBuyOrder($name, $city, $phone, $book_id, $user_id){
     global $pdo;
     $stmt = $pdo->prepare("INSERT INTO orders_Buy (name_buy, city, phone_number, book_id, id_user) VALUES (?, ?, ?, ?, ?)");
     return $stmt->execute([$name, $city, $phone, $book_id, $user_id]);
}
function createRentalOrder($name, $phone, $book_id, $user_id, $start_date, $end_date) {
      global $pdo;
      $stmt = $pdo->prepare("INSERT INTO orders_Rental (name_rental, phone_number, book_id, id_user, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
      return $stmt->execute([$name, $phone, $book_id, $user_id, $start_date, $end_date]);
}
function getUserOrders($user_id){
     global $pdo;
     $buy = $pdo->prepare("SELECT *, 'buy' as order_type FROM orders_Buy WHERE id_user = ? ORDER BY created_at DESC");
     $buy->execute([$user_id]);


     $rent = $pdo->prepare("SELECT *, 'rent' as order_type FROM orders_Rental WHERE id_user = ? ORDER BY created_at DESC");
     $rent->execute([$user_id]);
     return[
        'buy' => $buy->fetchAll(PDO::FETCH_ASSOC),
        'rent' => $rent->fetchAll(PDO::FETCH_ASSOC)
     ];
     
}
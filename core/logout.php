<?php


require_once 'functions.php';

$redirectUrl = ($_SESSION['user_role'] ?? '') === 'admin'
    ? '../admin/login.php'
    : '../views/login.php';

session_destroy();


$_SESSION = [];


header('Location: ' . $redirectUrl);
exit;

<?php


require_once 'functions.php';


session_destroy();


$_SESSION = [];


header('Location: ../admin/login.php');
exit;

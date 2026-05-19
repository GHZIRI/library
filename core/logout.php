<?php
require_once __DIR__ . '/functions.php';

// Destroy session and redirect to login
session_destroy();
redirect('../views/login.php');

<?php
require_once '../core/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Find user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Save user in session
        $_SESSION['user'] = $user;

        // Redirect based on role
        if ($user['role'] === 'admin') {
            redirect('../admin/dashboard.php');
        } else {
            redirect('catalogue.php');
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Library</title>
</head>
<body>

    <?php if ($error) { ?>
        <p style="color:red"><?= $error ?></p>
    <?php } ?>

    <form action="" method="POST">
        <input type="email"    name="email"    placeholder="Email"    required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>

    <a href="register.php">Don't have an account? Register here</a>

</body>
</html>
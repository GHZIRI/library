<?php
require_once '../core/functions.php';

// Already logged in → redirect
if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/dashboard.php' : 'catalogue.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;

            redirect($user['role'] === 'admin' ? '../admin/dashboard.php' : 'catalogue.php');
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="form-wrapper">
    <div class="form-box">
        <h1>📚 Library</h1>
        <p class="subtitle">Sign in to your account</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="you@example.com" required
                       value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:.5rem">
                Sign In
            </button>
        </form>

        <p class="form-footer-link">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

</body>
</html>
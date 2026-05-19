<?php
require_once '../core/functions.php';

// Already logged in → redirect
if (isLoggedIn()) {
    redirect('catalogue.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $name     = sanitize($_POST['username'] ?? '');
        $email    = sanitize($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = "This email is already registered.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO users (name_user, email, password) VALUES (?, ?, ?)"
                );

                if ($stmt->execute([$name, $email, $hashed])) {
                    // Auto login after register
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $_SESSION['user'] = $stmt->fetch();

                    redirect('catalogue.php');
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="form-wrapper">
    <div class="form-box">
        <h1>📚 Library</h1>
        <p class="subtitle">Create a new account</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
            <div class="form-group">
                <label for="username">Full Name</label>
                <input type="text" id="username" name="username"
                       placeholder="Your name" required
                       value="<?= sanitize($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="you@example.com" required
                       value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Min. 6 characters" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:.5rem">
                Create Account
            </button>
        </form>

        <p class="form-footer-link">
            Already have an account? <a href="login.php">Sign in</a>
        </p>
    </div>
</div>

</body>
</html>
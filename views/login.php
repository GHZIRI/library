<?php
session_start();

if(isset($_SESSION['user_id'])){
    header("Location: catalogue.php"); // Fix: "Locaton" → "Location"
    exit();
}

$errors    = $_SESSION['errors']    ?? [];
$old_email = $_SESSION['old_email'] ?? '';

unset($_SESSION['errors']);
unset($_SESSION['old_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Fix: CSS was missing -->
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Login</h1>

            <?php if(!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <form action="../core/functions.php" method="post">
                <input type="hidden" name="action" value="login">

                <div class="form-group"> <!-- Fix: "from-group" → "form-group" -->
                    <label>Email</label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($old_email) ?>"
                           placeholder="example@email.com" required> <!-- Fix: removed extra stray `">` -->
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Your password" required>
                </div>

                <button type="submit">Login</button> <!-- Fix: broken tag `<Login/button>` -->
            </form>

            <p>No account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
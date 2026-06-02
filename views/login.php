<?php
/**
 * Login Page
 * 
 * Login form for regular users.
 */

require_once '../core/functions.php';

// If user is already logged in, redirect to catalog
if (isLoggedIn()) {
    redirect('catalogue.php');
}

// Process login (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security error. Please try again.');
        redirect('login.php');
    }

    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Please fill in all fields.');
        redirect('login.php');
    }

    // Find the user
    $user = getUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        // Set sessions
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        
        setFlash('success', '✅ Successfully logged in.');
        redirect('catalogue.php');
    } else {
        setFlash('error', '❌ Incorrect email or password.');
        redirect('login.php');
    }
}

$error = getFlash('error');
$success = getFlash('success');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .navbar-links {
            display: flex;
            gap: 20px;
            list-style: none;
            flex-direction: row;
        }
        .form-group label {
            text-align: left;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="catalogue.php" class="navbar-brand">📚 Library</a>
            <ul class="navbar-links">
                <li><a href="catalogue.php">Home</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Login Box -->
    <div class="form-box" style="margin: 60px auto; max-width: 400px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        <h1 style="text-align: center; margin-bottom: 25px; font-weight: 700; color: var(--dark);">🔐 Login</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <!-- Email Address -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="margin-bottom: 8px; font-weight: 600; color: var(--dark);">Email Address *</label>
                <input type="email" name="email" placeholder="Enter your email address" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
            </div>

            <!-- Password -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="margin-bottom: 8px; font-weight: 600; color: var(--dark);">Password *</label>
                <input type="password" name="password" placeholder="Enter your password" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; border: none;">🔓 Login</button>

            <!-- Registration Link -->
            <p style="text-align: center; margin-top: 20px; color: var(--gray); font-size: 14px;">
                Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Register now</a>
            </p>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Library. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

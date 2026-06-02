<?php
/**
 * Register Page
 * 
 * Registration form for new users.
 */

require_once '../core/functions.php';

// If user is already logged in, redirect to catalog
if (isLoggedIn()) {
    redirect('catalogue.php');
}

// Process registration (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security error. Please try again.');
        redirect('register.php');
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Verify fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        setFlash('error', 'Please fill in all fields.');
        redirect('register.php');
    }

    if (strlen($password) < 6) {
        setFlash('error', 'Password must be at least 6 characters.');
        redirect('register.php');
    }

    if ($password !== $confirm_password) {
        setFlash('error', 'Passwords do not match.');
        redirect('register.php');
    }

    // Verify if email already exists
    if (getUserByEmail($email)) {
        setFlash('error', 'This email is already registered.');
        redirect('register.php');
    }

    // Create user
    $user_data = [
        'name' => $name,
        'email' => $email,
        'password' => $password
    ];

    if (createUser($user_data)) {
        setFlash('success', '✅ Account created successfully! You can now log in.');
        redirect('login.php');
    } else {
        setFlash('error', 'An error occurred. Please try again later.');
        redirect('register.php');
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
    <title>Register</title>
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
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Register Box -->
    <div class="form-box" style="margin: 40px auto; max-width: 450px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        <h1 style="text-align: center; margin-bottom: 25px; font-weight: 700; color: var(--dark);">📝 Register</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <!-- Full Name -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="margin-bottom: 6px; font-weight: 600; color: var(--dark);">Full Name *</label>
                <input type="text" name="name" placeholder="Enter your full name" required maxlength="100" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
            </div>

            <!-- Email Address -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="margin-bottom: 6px; font-weight: 600; color: var(--dark);">Email Address *</label>
                <input type="email" name="email" placeholder="Enter your email address" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
            </div>

            <!-- Password -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="margin-bottom: 6px; font-weight: 600; color: var(--dark);">Password *</label>
                <input type="password" name="password" placeholder="At least 6 characters" required minlength="6" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
            </div>

            <!-- Confirm Password -->
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="margin-bottom: 6px; font-weight: 600; color: var(--dark);">Confirm Password *</label>
                <input type="password" name="confirm_password" placeholder="Re-enter your password" required minlength="6" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 5px;">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; border: none;">✅ Register</button>

            <!-- Login Link -->
            <p style="text-align: center; margin-top: 20px; color: var(--gray); font-size: 14px;">
                Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Login now</a>
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

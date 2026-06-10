<?php
/**
 * Admin Login Page
 * 
 * Sets sessions and allows admin access only.
 */
session_start();

// Import database connection
require_once '../core/db.php';

// Message variables
$error_message = '';
$success_message = '';
$debug_message = '';

// If already logged in as admin, redirect to admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$debug_mode = false; // Set to true to debug

// Process login (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please enter your email and password.';
    } else {
        try {
            // Find admin user
            $stmt = $pdo->prepare("
                SELECT id, full_name, email, password, role
                FROM users
                WHERE email = ? AND role = 'admin'
            ");
            
            if ($debug_mode) {
                $debug_message = "Searching for: {$email}";
            }
            
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if (!$admin) {
                if ($debug_mode) {
                    $debug_message .= " - User not found";
                }
                $error_message = 'Incorrect email or password.';
            } else {
                if ($debug_mode) {
                    $debug_message .= " - User found: " . $admin['full_name'];
                }
                
                // Verify password
                $password_match = password_verify($password, $admin['password']);
                
                if ($debug_mode) {
                    $debug_message .= " - Password match check: " . ($password_match ? "Success" : "Failed");
                }
                
                if ($password_match) {
                    // Set session variables
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['user_name'] = $admin['full_name'];
                    $_SESSION['user_role'] = $admin['role'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = 'Incorrect email or password.';
                }
            }
        } catch (PDOException $e) {
            if ($debug_mode) {
                $error_message = 'Database error: ' . htmlspecialchars($e->getMessage());
            } else {
                $error_message = 'An error occurred. Please try again later.';
            }
        } catch (Exception $e) {
            if ($debug_mode) {
                $error_message = 'General error: ' . htmlspecialchars($e->getMessage());
            } else {
                $error_message = 'An error occurred. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>🔐 Admin Login</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($debug_message)): ?>
            <div style="background: #fff3cd; color: #856404; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ffeaa7; text-align: left;">
                🔍 DEBUG: <?php echo htmlspecialchars($debug_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">📧 Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="Enter your email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">🔑 Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
</body>
</html>

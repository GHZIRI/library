<?php
require_once '../core/functions.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = sanitize($_POST['username']);
    $email    = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $error = "This email is already registered!";
    } else {
        // Hash password before saving
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (name_user, email, password) VALUES (?, ?, ?)");

        if ($stmt->execute([$name, $email, $hashed])) {
            redirect('catalogue.php');
        } else {
            $error = "Something went wrong. Please try again.";
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
</head>
<body>

    <?php if ($error) { ?>
    <p style="color:red"><?= $error ?></p>
<?php } ?>

    <form action="" method="POST">
        <input type="text"     name="username" placeholder="Username" required><br>
        <input type="email"    name="email"    placeholder="Email"    required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>

    <a href="login.php">Already have an account? Login</a>

</body>
</html>
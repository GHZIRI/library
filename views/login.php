<?php
session_start();

if(isset($_SESSION['user_id'])){
    header("Locaton:catalogue.php");
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
    <title>Document</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Login</h1>
             <?php 
             if(!empty($errors)){
                foreach($errors as $error){
                    echo "<p class='success'>Acount created! Login now.<p> ";
                }
             }
              ?>
              <form action="../core/functions.php" method="post">
                <input type="hidden" name="action" value="login">

                <div class="from-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($old_email) ?>"
                       placeholder="example@email.com" required>">
                </div>
                 
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Your pasword" required>
                </div>
                 <button type="submit" class="bt-parimay"><Login/button>
              </form>
              <p>No account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FarmDirect</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="logo">🌾 FarmDirect</a>
</nav>

<div class="auth-container">
    <h2 class="text-center" style="margin-bottom: 1.5rem;">Welcome Back</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); endif; ?>
        
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); endif; ?>

    <form action="includes/auth_handler.php" method="POST">
        <input type="hidden" name="action" value="login">
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        
        <p class="text-center" style="margin-top: 1rem;">
            Don't have an account? <a href="register.php" style="color: var(--primary);">Sign Up</a>
        </p>
    </form>
</div>

</body>
</html>

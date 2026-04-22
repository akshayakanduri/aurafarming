<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmDirect - Connect Directly with Farmers</title>
    <meta name="description" content="Buy fresh crops directly from farmers at fair prices.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="logo">🌾 FarmDirect</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="marketplace.php">Marketplace</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'farmer'): ?>
                <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
            <?php endif; ?>
            <a href="includes/auth_handler.php?action=logout" class="btn btn-outline">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<section class="hero">
    <div class="hero-content">
        <h1>Fresh from Farm to Your Table</h1>
        <p>Eliminate middlemen. Connect directly with local farmers to buy fresh fruits, vegetables, and grains at fair prices. Support fair trade today.</p>
        <a href="marketplace.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.8rem 1.5rem;">Browse Crops</a>
        <a href="register.php" class="btn btn-outline" style="font-size: 1.1rem; padding: 0.8rem 1.5rem; margin-left: 1rem;">Join as Farmer</a>
    </div>
    <div>
        <!-- A simple CSS shape representing agriculture -->
        <div style="width: 300px; height: 300px; background: var(--primary); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-hover);">
            <span style="font-size: 5rem; transform: rotate(45deg);">🚜</span>
        </div>
    </div>
</section>

<div class="container text-center mt-2">
    <h2 class="mb-2">Why Choose FarmDirect?</h2>
    <div class="grid">
        <div class="card">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Fair Pricing</h3>
            <p class="text-muted">No middlemen means better margins for farmers and lower prices for customers.</p>
        </div>
        <div class="card">
            <h3 style="color: var(--accent); margin-bottom: 1rem;">Urgent Sales</h3>
            <p class="text-muted">Help reduce food waste by purchasing crops at high risk of spoilage at heavy discounts.</p>
        </div>
        <div class="card">
            <h3 style="color: var(--secondary); margin-bottom: 1rem;">Direct Connection</h3>
            <p class="text-muted">Know exactly who grew your food and support your local agricultural community.</p>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>

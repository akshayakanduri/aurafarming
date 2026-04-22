<?php
// onboarding.php
require_once 'includes/auth_handler.php';
// We allow ANY logged-in user to see onboarding now
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding - FarmDirect</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav style="box-shadow: none; border-bottom: 1px solid var(--border);">
    <a href="index.php" class="logo">🌾 FarmDirect</a>
    <div class="nav-links">
        <a href="includes/auth_handler.php?action=logout" style="color: var(--text-muted);">Logout</a>
    </div>
</nav>

<div class="onboarding-container">
    <div class="onboarding-left">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--primary-dark);">How do you want to use FarmDirect?</h1>
        <p style="font-size: 1.2rem; color: var(--text-muted); margin-bottom: 3rem;">We’ll personalize your experience based on your needs.</p>

        <form action="includes/mode_handler.php" method="POST" id="onboardingForm">
            <input type="hidden" name="buyer_mode" id="buyer_mode" value="retail">

            <div class="mode-card active" data-mode="retail">
                <div class="mode-icon">🛒</div>
                <div class="mode-text">
                    <h3>Buy for Daily Use</h3>
                    <p>Browse fresh fruits and vegetables directly from farmers</p>
                    <span class="badge badge-retail" style="position: static; display: inline-block; margin-top: 5px;">Retail Mode</span>
                </div>
            </div>

            <div class="mode-card" data-mode="wholesale">
                <div class="mode-icon">🏭</div>
                <div class="mode-text">
                    <h3>Buy in Bulk</h3>
                    <p>Purchase large quantities at discounted wholesale prices</p>
                    <span class="badge badge-wholesale" style="position: static; display: inline-block; margin-top: 5px;">Wholesale Mode</span>
                </div>
            </div>
            
            <div class="mode-card" data-mode="farmer">
                <div class="mode-icon">👨‍🌾</div>
                <div class="mode-text">
                    <h3>Sell Crops</h3>
                    <p>List your fresh produce and connect with buyers</p>
                    <span class="badge badge-urgent" style="position: static; display: inline-block; margin-top: 5px; background: var(--primary-dark);">Farmer Mode</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 2rem; padding: 1rem 2rem; font-size: 1.1rem; width: 100%;">Continue</button>
        </form>
    </div>
    
    <div class="onboarding-right" id="previewPanel">
        <!-- Retail Preview Mockup -->
        <div class="preview-mockup retail-preview active">
            <div class="mockup-header">Customer Marketplace</div>
            <div class="mockup-body">
                <div class="mockup-search">🔍 Search tomatoes, fruits...</div>
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                    <div class="mockup-card">
                        <div class="mockup-img" style="background: #ffcdd2;">🍎</div>
                        <div style="font-weight: bold; margin-top: 10px;">Fresh Apples</div>
                        <div style="color: var(--primary);">₹50 / kg</div>
                        <div class="mockup-btn">Add to Cart</div>
                    </div>
                    <div class="mockup-card">
                        <div class="mockup-img" style="background: #c8e6c9;">🥦</div>
                        <div style="font-weight: bold; margin-top: 10px;">Organic Broccoli</div>
                        <div style="color: var(--primary);">₹40 / kg</div>
                        <div class="mockup-btn">Add to Cart</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wholesale Preview Mockup -->
        <div class="preview-mockup wholesale-preview">
            <div class="mockup-header" style="background: var(--secondary);">Shopkeeper Dashboard</div>
            <div class="mockup-body">
                <div class="mockup-banner">Bulk Orders Only (Min. 50kg)</div>
                <div style="background: white; padding: 1rem; border-radius: 8px; margin-top: 1rem; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="font-size: 2rem;">🥔</div>
                            <div>
                                <div style="font-weight: bold; font-size: 1.2rem;">Potatoes - Bulk Sack</div>
                                <div style="color: var(--text-muted);">Available: 500kg</div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="text-decoration: line-through; color: var(--text-muted); font-size: 0.9rem;">Retail: ₹30/kg</div>
                            <div style="color: var(--secondary); font-weight: bold; font-size: 1.2rem;">Wholesale: ₹18/kg</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <div class="mockup-btn" style="flex: 1; background: var(--secondary); color: white;">Order Now</div>
                        <div class="mockup-btn" style="flex: 1; background: transparent; color: var(--secondary); border: 1px solid var(--secondary);">Negotiate</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Farmer Preview Mockup -->
        <div class="preview-mockup farmer-preview">
            <div class="mockup-header" style="background: var(--primary-dark);">Farmer Dashboard</div>
            <div class="mockup-body">
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="flex: 1; background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 0.9rem; color: var(--text-muted);">Revenue</div>
                        <div style="font-size: 1.5rem; color: var(--primary); font-weight: bold;">₹12,400</div>
                    </div>
                    <div style="flex: 1; background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 0.9rem; color: var(--text-muted);">Listings</div>
                        <div style="font-size: 1.5rem; font-weight: bold;">8</div>
                    </div>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px;">
                    <h4 style="margin-bottom: 0.5rem;">Add New Crop</h4>
                    <div style="height: 30px; background: var(--background); border-radius: 4px; margin-bottom: 10px;"></div>
                    <div style="height: 30px; background: var(--background); border-radius: 4px; margin-bottom: 10px;"></div>
                    <div class="mockup-btn" style="width: 100%; text-align: center;">List Crop</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.mode-card');
        const buyerModeInput = document.getElementById('buyer_mode');
        const retailPreview = document.querySelector('.retail-preview');
        const wholesalePreview = document.querySelector('.wholesale-preview');
        const farmerPreview = document.querySelector('.farmer-preview');

        cards.forEach(card => {
            card.addEventListener('click', () => {
                // Remove active classes
                cards.forEach(c => c.classList.remove('active'));
                retailPreview.classList.remove('active');
                wholesalePreview.classList.remove('active');
                farmerPreview.classList.remove('active');

                // Set active class
                card.classList.add('active');
                
                // Update hidden input
                const mode = card.getAttribute('data-mode');
                buyerModeInput.value = mode;

                // Show respective preview
                if(mode === 'retail') {
                    retailPreview.classList.add('active');
                } else if(mode === 'wholesale') {
                    wholesalePreview.classList.add('active');
                } else {
                    farmerPreview.classList.add('active');
                }
            });
        });
    });
</script>
</body>
</html>

<?php
// marketplace.php
require_once 'includes/auth_handler.php';
require_once 'includes/xml_db.php';

$user = getCurrentUser();
$isCustomer = $user && ((string)$user->role === 'customer');
$buyerMode = $_SESSION['buyer_mode'] ?? 'retail';

// Handle Category Filtering
$selectedCategory = $_GET['category'] ?? 'all';

// Fetch Crops
$cropsXml = loadXml('crops');
$usersXml = loadXml('users');

$allCrops = [];
foreach ($cropsXml->crop as $crop) {
    if ((string)$crop->status !== 'available') continue;

    // Mode Filtering
    $saleType = (string)$crop->sale_type;
    if ($buyerMode === 'wholesale' && $saleType === 'retail') {
        continue;
    }
    if ($buyerMode === 'retail' && $saleType === 'wholesale') {
        continue;
    }

    // Category Filtering
    $cropCat = strtolower((string)$crop->category);
    if ($selectedCategory !== 'all' && $cropCat !== $selectedCategory) {
        continue;
    }

    // Find Farmer Name
    $farmerName = 'Farmer';
    foreach ($usersXml->user as $u) {
        if ((string)$u->id === (string)$crop->farmer_id) {
            $farmerName = (string)$u->name;
            break;
        }
    }

    $allCrops[] = [
        'id' => (string)$crop->id,
        'name' => (string)$crop->name,
        'category' => $cropCat,
        'quantity' => (float)$crop->quantity,
        'price' => (float)$crop->price,
        'sale_type' => $saleType,
        'farmer_name' => $farmerName,
        'location' => (string)$crop->location,
        'image' => (string)$crop->image // Grab image from XML
    ];
}

function getImage($name) {
    $name = strtolower(trim($name));
    if (strpos($name, 'tomato') !== false) return 'assets/images/products/tomato.jpg';
    if (strpos($name, 'potato') !== false) return 'assets/images/products/potato.jpg';
    if (strpos($name, 'onion') !== false) return 'assets/images/products/onion.jpg';
    if (strpos($name, 'carrot') !== false) return 'assets/images/products/carrot.jpg';
    if (strpos($name, 'chili') !== false || strpos($name, 'chilli') !== false) return 'assets/images/products/chilli.jpg';
    if (strpos($name, 'mango') !== false) return 'assets/images/products/mango.jpg';
    if (strpos($name, 'rice') !== false) return 'assets/images/products/rice.jpg';
    return 'assets/images/products/default.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - FarmDirect</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background: var(--background);">

<nav>
    <a href="index.php" class="logo">🌾 FarmDirect</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <?php if($user): ?>
            <?php if((string)$user->role === 'farmer'): ?>
                <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
            <?php endif; ?>
            
            <?php if($isCustomer): ?>
                <a href="includes/mode_handler.php?action=toggle_mode" class="btn btn-outline" style="border-color: var(--secondary); color: var(--secondary);">
                    Switch to <?= $buyerMode === 'retail' ? 'Wholesale' : 'Retail' ?>
                </a>
                <div class="cart-icon" id="cartIcon" style="margin-left: 1rem;">
                    🛒 Cart <span class="cart-count" id="cartCount">0</span>
                </div>
            <?php endif; ?>
            
            <a href="includes/auth_handler.php?action=logout" class="btn btn-outline" style="margin-left: 1rem;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Cart Modal -->
<div id="cartModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: var(--surface); width: 90%; max-width: 500px; margin: 100px auto; border-radius: var(--radius); padding: 2rem; position: relative;">
        <button id="closeCart" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <h2>Your Cart</h2>
        <div id="cartItems" style="margin: 1.5rem 0; max-height: 300px; overflow-y: auto;"></div>
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2rem; margin-bottom: 1rem; border-top: 2px solid var(--border); padding-top: 1rem;">
            <span>Total:</span>
            <span>₹<span id="cartTotal">0</span></span>
        </div>
        <button id="checkoutBtn" class="btn btn-primary" style="width: 100%;">Proceed to Checkout</button>
    </div>
</div>

<div class="marketplace-layout">
    
    <!-- LEFT SIDEBAR -->
    <div class="marketplace-sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem; color: var(--text-muted);">Categories</h3>
        <a href="marketplace.php?category=all" class="sidebar-category <?= $selectedCategory === 'all' ? 'active' : '' ?>">
            <span>🛒</span> All Products
        </a>
        <a href="marketplace.php?category=fruits" class="sidebar-category <?= $selectedCategory === 'fruits' ? 'active' : '' ?>">
            <span>🍎</span> Fruits
        </a>
        <a href="marketplace.php?category=vegetables" class="sidebar-category <?= $selectedCategory === 'vegetables' ? 'active' : '' ?>">
            <span>🥦</span> Vegetables
        </a>
        <a href="marketplace.php?category=grains" class="sidebar-category <?= $selectedCategory === 'grains' ? 'active' : '' ?>">
            <span>🌾</span> Grains
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="marketplace-main">
        
        <?php if(!$isCustomer): ?>
            <div style="background: #fff3e0; color: var(--accent); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px dashed var(--accent);">
                Log in as a customer to place orders.
            </div>
        <?php endif; ?>

        <?php if($buyerMode === 'wholesale'): ?>
            <div style="background: var(--secondary); color: white; padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow);">
                <div>
                    <h2 style="margin-bottom: 0.5rem;">Wholesale Dashboard</h2>
                    <p>Purchase directly from farmers in bulk quantities. Minimum order: 20kg.</p>
                </div>
                <div style="font-size: 3rem;">🏭</div>
            </div>
        <?php else: ?>
            <!-- TOP SCROLLABLE CATEGORIES -->
            <div class="top-categories">
                <a href="marketplace.php?category=all" class="top-cat-card <?= $selectedCategory === 'all' ? 'active' : '' ?>">
                    <div class="top-cat-icon">🛒</div>
                    <span style="font-size: 0.85rem; font-weight: 500;">All</span>
                </a>
                <a href="marketplace.php?category=fruits" class="top-cat-card <?= $selectedCategory === 'fruits' ? 'active' : '' ?>">
                    <div class="top-cat-icon">🍎</div>
                    <span style="font-size: 0.85rem; font-weight: 500;">Fruits</span>
                </a>
                <a href="marketplace.php?category=vegetables" class="top-cat-card <?= $selectedCategory === 'vegetables' ? 'active' : '' ?>">
                    <div class="top-cat-icon">🥦</div>
                    <span style="font-size: 0.85rem; font-weight: 500;">Vegetables</span>
                </a>
                <a href="marketplace.php?category=grains" class="top-cat-card <?= $selectedCategory === 'grains' ? 'active' : '' ?>">
                    <div class="top-cat-icon">🌾</div>
                    <span style="font-size: 0.85rem; font-weight: 500;">Grains</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- PRODUCT GRID -->
        <h2 style="margin-bottom: 1rem;"><?= ucfirst($selectedCategory) ?> Products</h2>
        
        <?php if (empty($allCrops)): ?>
            <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🍃</div>
                <h3 style="color: var(--text-muted);">No fresh crops available in this category.</h3>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach($allCrops as $crop): ?>
                    <?php 
                        $isUrgent = ($crop['sale_type'] === 'urgent');
                        $isWholesale = ($buyerMode === 'wholesale');
                        
                        // Robust Image Display Logic
                        $imageSrc = !empty($crop['image']) ? $crop['image'] : getImage($crop['name']);
                        if (!file_exists($imageSrc)) {
                            $imageSrc = 'assets/images/products/default.jpg';
                        }
                        
                        // Fake discount calculation for UI
                        $originalPrice = $crop['price'] * 1.2; 
                        
                        // Wholesale setup
                        $minQty = $isWholesale ? 20 : 1;
                        $displayPrice = $crop['price'];
                        $buttonClass = $isWholesale ? 'btn-wholesale' : '';
                        $buttonText = $isWholesale ? 'ORDER BULK' : 'ADD';
                    ?>
                    <div class="product-card">
                        <div class="product-img-container">
                            <?php if($isUrgent): ?>
                                <span class="product-tag tag-urgent">Urgent Sale</span>
                            <?php elseif($isWholesale): ?>
                                <span class="product-tag tag-wholesale">Bulk Only</span>
                            <?php else: ?>
                                <span class="product-tag tag-fresh">Fresh</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($crop['name']) ?>" class="product-image">
                        </div>
                        
                        <div class="product-details">
                            <h3 class="product-title"><?= htmlspecialchars($crop['name']) ?></h3>
                            <div class="product-qty">Stock: <?= $crop['quantity'] ?>kg</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">👨‍🌾 <?= htmlspecialchars($crop['farmer_name']) ?></div>
                            
                            <?php if($isWholesale): ?>
                                <div style="font-size: 0.8rem; font-weight: bold; color: var(--secondary); margin-bottom: 0.5rem;">Min Order: 20kg</div>
                                
                                <div style="margin-bottom: 0.5rem;">
                                    <label for="qty_<?= $crop['id'] ?>" style="font-size: 0.85rem; font-weight: bold; color: var(--text);">Quantity (kg):</label>
                                    <input type="number" id="qty_<?= $crop['id'] ?>" min="20" max="<?= $crop['quantity'] ?>" value="20" class="qty-input"
                                           style="width: 100%; padding: 6px; border: 1px solid var(--border); border-radius: var(--radius); margin-top: 2px;"
                                           oninput="updateWholesaleTotal('<?= $crop['id'] ?>', <?= $displayPrice ?>, <?= $crop['quantity'] ?>)">
                                </div>
                                <div id="total_<?= $crop['id'] ?>" style="font-size: 0.95rem; font-weight: bold; color: var(--primary); margin-bottom: 0.5rem;">
                                    Total: ₹<?= number_format($displayPrice * 20, 2) ?>
                                </div>
                                <div id="error_<?= $crop['id'] ?>" style="color: red; font-size: 0.8rem; display: none; margin-bottom: 0.5rem;"></div>
                            <?php endif; ?>

                            <div class="product-bottom">
                                <div>
                                    <span class="product-price-old">₹<?= number_format($originalPrice, 2) ?></span>
                                    <span class="product-price">₹<?= number_format($displayPrice, 2) ?>/kg</span>
                                </div>
                                <?php if($isWholesale): ?>
                                    <button id="btn_<?= $crop['id'] ?>" class="btn-add <?= $buttonClass ?>" onclick="addWholesaleToCart('<?= $crop['id'] ?>', '<?= addslashes($crop['name']) ?>', <?= $displayPrice ?>)">
                                        <?= $buttonText ?>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-add <?= $buttonClass ?>" onclick="addToCart('<?= $crop['id'] ?>', '<?= addslashes($crop['name']) ?>', <?= $displayPrice ?>, <?= $minQty ?>)">
                                        <?= $buttonText ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    const IS_CUSTOMER = <?= $isCustomer ? 'true' : 'false' ?>;
    document.addEventListener('DOMContentLoaded', () => {
        const originalAddToCart = window.addToCart;
        window.addToCart = function(id, name, price, qty) {
            if (!IS_CUSTOMER) {
                alert('Please log in as a customer to add items to your cart.');
                window.location.href = 'login.php';
                return;
            }
            if(typeof originalAddToCart === 'function') {
                originalAddToCart(id, name, price, qty);
            }
        }
    });

    function updateWholesaleTotal(cropId, pricePerKg, maxStock) {
        const qtyInput = document.getElementById('qty_' + cropId);
        const totalDiv = document.getElementById('total_' + cropId);
        const errorDiv = document.getElementById('error_' + cropId);
        const btn = document.getElementById('btn_' + cropId);
        
        let qty = parseFloat(qtyInput.value);
        if (isNaN(qty)) qty = 0;
        
        let hasError = false;
        
        if (qty < 20) {
            errorDiv.textContent = "Minimum order is 20kg";
            errorDiv.style.display = "block";
            hasError = true;
        } else if (qty > maxStock) {
            errorDiv.textContent = "Quantity exceeds available stock";
            errorDiv.style.display = "block";
            hasError = true;
        } else {
            errorDiv.style.display = "none";
        }
        
        if (hasError) {
            btn.disabled = true;
            btn.style.opacity = "0.5";
            btn.style.cursor = "not-allowed";
        } else {
            btn.disabled = false;
            btn.style.opacity = "1";
            btn.style.cursor = "pointer";
            totalDiv.textContent = "Total: ₹" + (qty * pricePerKg).toFixed(2);
        }
    }

    function addWholesaleToCart(id, name, price) {
        const qtyInput = document.getElementById('qty_' + id);
        let qty = parseFloat(qtyInput.value);
        if (isNaN(qty) || qty < 20) {
            alert("Minimum order is 20kg");
            return;
        }
        addToCart(id, name, price, qty);
    }
</script>
<script src="assets/js/app.js"></script>
</body>
</html>

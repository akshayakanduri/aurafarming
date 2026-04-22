<?php
// dashboard.php
require_once 'includes/auth_handler.php';
requireAuth('farmer');

$user = getCurrentUser();
$userId = (string)$user->id;

// Handle Crop Deletion
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $file = "data/crops.xml";
    if (file_exists($file)) {
        $xml = simplexml_load_file($file);
        foreach ($xml->crop as $crop) {
            if ((string)$crop->id === $deleteId && (string)$crop->farmer_id === $userId) {
                $dom = dom_import_simplexml($crop);
                $dom->parentNode->removeChild($dom);
                $xml->asXML($file);
                break;
            }
        }
    }
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = strtolower(trim($_POST['crop_name'] ?? ''));
    // Simple plural removal
    if (substr($name, -2) === 'es' && $name !== 'tomatoes') {
        $name = substr($name, 0, -2);
    } elseif (substr($name, -1) === 's' && $name !== 'basmati rice' && $name !== 'tomatoes') {
        $name = substr($name, 0, -1);
    }
    
    $category = strtolower(trim($_POST['category'] ?? ''));
    $quantity = (float)($_POST['quantity'] ?? 0);
    $type = strtolower($_POST['sale_type'] ?? 'retail');
    $location = trim($_POST['location'] ?? '');
    $price = (float)($_POST['price'] ?? 0);

    // Determine Image Path strictly based on keywords
    if (!function_exists('getProductImageFilename')) {
        function getProductImageFilename($productName) {
            if (strpos($productName, 'tomato') !== false) return 'tomato.jpg';
            if (strpos($productName, 'potato') !== false) return 'potato.jpg';
            if (strpos($productName, 'onion') !== false) return 'onion.jpg';
            if (strpos($productName, 'carrot') !== false) return 'carrot.jpg';
            if (strpos($productName, 'chili') !== false || strpos($productName, 'chilli') !== false) return 'chilli.jpg';
            if (strpos($productName, 'mango') !== false) return 'mango.jpg';
            if (strpos($productName, 'rice') !== false) return 'rice.jpg';
            return 'default.jpg';
        }
    }
    
    $imageFilename = getProductImageFilename($name);
    $imagePath = "assets/images/products/" . $imageFilename;

    $file = "data/crops.xml";

    if (!file_exists($file)) {
        $xml = new SimpleXMLElement("<crops></crops>");
        $xml->asXML($file);
    }

    $xml = simplexml_load_file($file);

    // Check for Duplicates (same farmer, name, category, sale_type, available)
    $found = false;
    foreach ($xml->crop as $c) {
        if ((string)$c->farmer_id === $userId && 
            strtolower(trim((string)$c->name)) === $name && 
            strtolower(trim((string)$c->category)) === $category &&
            strtolower(trim((string)$c->sale_type)) === $type &&
            (string)$c->status === 'available') {
            
            // Duplicate found: update quantity and price instead of adding a new row
            $c->quantity = (string)((float)$c->quantity + $quantity);
            $c->price = (string)$price;
            
            if (!isset($c->image)) {
                $c->addChild("image", htmlspecialchars($imagePath));
            } else {
                $c->image = htmlspecialchars($imagePath);
            }
            $found = true;
            break;
        }
    }

    if (!$found) {
        $crop = $xml->addChild("crop");
        $crop->addChild("id", uniqid("c_"));
        $crop->addChild("name", htmlspecialchars($name));
        $crop->addChild("category", htmlspecialchars($category));
        $crop->addChild("quantity", (string)$quantity);
        $crop->addChild("sale_type", htmlspecialchars($type));
        $crop->addChild("location", htmlspecialchars($location));
        $crop->addChild("price", (string)$price);
        $crop->addChild("farmer_id", $userId);
        $crop->addChild("status", "available");
        $crop->addChild("image", htmlspecialchars($imagePath)); // Save the image path
        $crop->addChild("date_added", date("Y-m-d H:i:s"));
    }

    $xml->asXML($file);

    header("Location: dashboard.php");
    exit;
}

// Calculate basic analytics for farmer
$cropsXml = loadXml('crops');
$ordersXml = loadXml('orders');

$totalListings = 0;
$activeListings = 0;
foreach ($cropsXml->crop as $crop) {
    if ((string)$crop->farmer_id === $userId) {
        $totalListings++;
        if ((string)$crop->status === 'available') {
            $activeListings++;
        }
    }
}

$totalSales = 0;
$revenue = 0;
foreach ($ordersXml->order as $order) {
    if ((string)$order->farmer_id === $userId) {
        $totalSales++;
        $revenue += (float)$order->total;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FarmDirect</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="logo">🌾 FarmDirect</a>
    <div class="nav-links">
        <span style="font-weight: 600; color: var(--primary);">👨‍🌾 Welcome, <?= htmlspecialchars((string)$user->name) ?>!</span>
        <a href="marketplace.php">Marketplace</a>
        <a href="includes/auth_handler.php?action=logout" class="btn btn-outline">Logout</a>
    </div>
</nav>

<div class="container mt-2">
    <h2 class="mb-2">Your Dashboard Analytics</h2>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <p>₹<?= number_format($revenue, 2) ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Listings</h3>
            <p><?= $activeListings ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p><?= $totalSales ?></p>
        </div>
    </div>

    <div class="grid">
        <div class="card" style="grid-column: span 1;">
            <h3 class="mb-2">Add New Crop</h3>
            <form id="addCropForm" method="POST" action="">
                <div class="form-group">
                    <label>Crop Name</label>
                    <input type="text" name="crop_name" class="form-control" required placeholder="e.g. Tomatoes, Basmati Rice">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="category" class="form-control" required>
                        <option value="">Select Category...</option>
                        <option value="fruits">Fruits</option>
                        <option value="vegetables">Vegetables</option>
                        <option value="grains">Grains</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity Available (kg)</label>
                    <input type="number" name="quantity" id="qty" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label>Sale Type</label>
                    <select name="sale_type" id="sale_type" class="form-control" required>
                        <option value="retail">Retail (Standard)</option>
                        <option value="wholesale">Wholesale (Bulk)</option>
                        <option value="urgent">Urgent Sale (Discounted)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars((string)$user->location) ?>" required>
                </div>
                <div class="form-group">
                    <label>Your Price per kg (₹)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                    <small style="color: var(--primary); font-weight: 600; display: block; margin-top: 5px;">
                        Suggested Price: <span id="suggested_price">₹0.00</span>
                    </small>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">List Crop</button>
            </form>
        </div>
        
        <div class="card" style="grid-column: span 2;">
            <h3 class="mb-2">Your Current Listings</h3>
            <?php if ($totalListings === 0): ?>
                <p class="text-muted">You haven't listed any crops yet.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                                <th style="padding: 10px;">Name</th>
                                <th>Category</th>
                                <th>Qty (kg)</th>
                                <th>Price (₹)</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cropsXml->crop as $crop): ?>
                                <?php if ((string)$crop->farmer_id === $userId): ?>
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 10px; font-weight: 500;"><?= htmlspecialchars((string)$crop->name) ?></td>
                                        <td><?= ucfirst((string)$crop->category) ?></td>
                                        <td><?= (string)$crop->quantity ?></td>
                                        <td><?= (string)$crop->price ?></td>
                                        <td><span class="badge badge-<?= (string)$crop->sale_type ?>" style="position: static; font-size: 0.7rem; padding: 2px 8px;"><?= strtoupper((string)$crop->sale_type) ?></span></td>
                                        <td style="color: <?= (string)$crop->status === 'available' ? 'var(--primary)' : 'var(--text-muted)' ?>; font-weight: 600;">
                                            <?= ucfirst(str_replace('_', ' ', (string)$crop->status)) ?>
                                        </td>
                                        <td>
                                            <a href="dashboard.php?delete_id=<?= urlencode((string)$crop->id) ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Remove</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>

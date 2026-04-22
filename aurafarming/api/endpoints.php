<?php
// api/endpoints.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/xml_db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Crop
    if ($action === 'add_crop') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $xml = loadXml('crops');
        $crop = $xml->addChild('crop');
        $crop->addChild('id', uniqid('c_'));
        $crop->addChild('farmer_id', $_SESSION['user_id']);
        $crop->addChild('name', htmlspecialchars($_POST['name']));
        $crop->addChild('quantity', (float)$_POST['quantity']);
        $crop->addChild('price', (float)$_POST['price']);
        $crop->addChild('category', htmlspecialchars($_POST['category']));
        $crop->addChild('sale_type', htmlspecialchars($_POST['sale_type'])); // Retail, Wholesale, Urgent
        $crop->addChild('location', htmlspecialchars($_POST['location']));
        $crop->addChild('status', 'available');
        $crop->addChild('date_added', date('Y-m-d H:i:s'));

        saveXml('crops', $xml);
        echo json_encode(['status' => 'success', 'message' => 'Crop added successfully']);
        exit;
    }

    // Place Order
    if ($action === 'place_order') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['items'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            exit;
        }

        $ordersXml = loadXml('orders');
        $cropsXml = loadXml('crops');
        
        $totalCost = 0;

        foreach ($data['items'] as $item) {
            $cropId = $item['id'];
            $qty = (float)$item['quantity'];

            // Find crop and update quantity
            foreach ($cropsXml->crop as $crop) {
                if ((string)$crop->id === $cropId) {
                    $currentQty = (float)$crop->quantity;
                    if ($currentQty >= $qty) {
                        $crop->quantity = $currentQty - $qty;
                        if ($crop->quantity == 0) {
                            $crop->status = 'sold_out';
                        }
                        
                        // Add to orders
                        $order = $ordersXml->addChild('order');
                        $order->addChild('id', uniqid('o_'));
                        $order->addChild('customer_id', $_SESSION['user_id']);
                        $order->addChild('farmer_id', (string)$crop->farmer_id);
                        $order->addChild('crop_id', $cropId);
                        $order->addChild('quantity', $qty);
                        $order->addChild('price_per_unit', (string)$crop->price);
                        $order->addChild('total', $qty * (float)$crop->price);
                        $order->addChild('date', date('Y-m-d H:i:s'));
                        
                        $totalCost += ($qty * (float)$crop->price);
                    }
                    break;
                }
            }
        }

        saveXml('orders', $ordersXml);
        saveXml('crops', $cropsXml);

        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully', 'total' => $totalCost]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get Crops
    if ($action === 'get_crops') {
        $xml = loadXml('crops');
        $usersXml = loadXml('users');
        $crops = [];
        
        foreach ($xml->crop as $crop) {
            if ((string)$crop->status !== 'available') continue;
            
            $farmerName = 'Unknown Farmer';
            $farmerRating = 'N/A';
            foreach ($usersXml->user as $user) {
                if ((string)$user->id === (string)$crop->farmer_id) {
                    $farmerName = (string)$user->name;
                    $farmerRating = (string)$user->rating;
                    break;
                }
            }

            $crops[] = [
                'id' => (string)$crop->id,
                'farmer_id' => (string)$crop->farmer_id,
                'farmer_name' => $farmerName,
                'farmer_rating' => $farmerRating,
                'name' => (string)$crop->name,
                'quantity' => (float)$crop->quantity,
                'price' => (float)$crop->price,
                'category' => (string)$crop->category,
                'sale_type' => (string)$crop->sale_type,
                'location' => (string)$crop->location,
                'date_added' => (string)$crop->date_added
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $crops]);
        exit;
    }

    // Dynamic Pricing Suggestion
    if ($action === 'suggest_price') {
        $category = $_GET['category'] ?? '';
        $qty = (float)($_GET['qty'] ?? 0);
        $urgency = $_GET['urgency'] ?? 'no';

        // Base dummy logic for suggested pricing
        $basePrices = [
            'fruits' => 50, // per kg
            'vegetables' => 30,
            'grains' => 40
        ];
        
        $base = $basePrices[$category] ?? 40;
        
        // Wholesale discount
        if ($qty > 50) {
            $base *= 0.8; // 20% off
        } elseif ($qty > 20) {
            $base *= 0.9; // 10% off
        }

        // Urgency discount
        if ($urgency === 'yes') {
            $base *= 0.6; // 40% off for urgent sale (spoilage risk)
        }

        echo json_encode(['status' => 'success', 'suggested_price' => round($base, 2)]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/Order.php');
require_once(__DIR__ . '/../classes/OrderItem.php');
require_once(__DIR__ . '/../classes/Cart.php');

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$orderItem = new OrderItem($db);
$cart = new Cart($db);

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    if (strpos($request_uri, '/checkout') !== false && $method === 'POST') {
        // Checkout - create order from cart
        $data = json_decode(file_get_contents("php://input"));
        
        // Get cart items
        $cart->user_id = $user_id;
        $stmt = $cart->getByUser();
        $cart_items = [];
        $total_price = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item_total = $row['price'] * $row['quantity'];
            $total_price += $item_total;
            $cart_items[] = $row;
        }

        if (empty($cart_items)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cart is empty']);
            exit;
        }

        // Create order
        $order->user_id = $user_id;
        $order->total_price = $total_price;
        $order->status = 'pending';

        if($order->create()) {
            $order_id = $db->lastInsertId();
            
            // Create order items
            foreach ($cart_items as $item) {
                $orderItem->order_id = $order_id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->price_at_purchase = $item['price'];
                $orderItem->create();
            }

            // Clear cart
            $cart->clear();

            echo json_encode([
                'success' => true,
                'data' => [
                    'order_id' => (int)$order_id,
                    'total_price' => (float)$total_price,
                    'status' => 'pending'
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create order']);
        }

    } elseif ($method === 'GET') {
        // Check if requesting single order
        $path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
        $order_id = end($path_parts);
        
        if ($order_id && is_numeric($order_id)) {
            // Get single order
            $order->id = $order_id;
            $result = $order->readOne();
            
            if($result && $result['user_id'] == $user_id) {
                // Get order items
                $orderItem->order_id = $order_id;
                $items_stmt = $orderItem->getByOrder();
                $items = [];
                
                while ($item = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $items[] = [
                        'id' => (int)$item['id'],
                        'product_id' => (int)$item['product_id'],
                        'title' => $item['title'],
                        'slug' => $item['slug'],
                        'image' => $item['image'],
                        'quantity' => (int)$item['quantity'],
                        'price_at_purchase' => (float)$item['price_at_purchase']
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$order->id,
                        'user_id' => (int)$order->user_id,
                        'total_price' => (float)$order->total_price,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'items' => $items
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Order not found']);
            }
        } else {
            // Get user's orders
            $order->user_id = $user_id;
            $stmt = $order->readByUser();
            $orders = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $orders[] = [
                    'id' => (int)$row['id'],
                    'user_id' => (int)$row['user_id'],
                    'total_price' => (float)$row['total_price'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at']
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => $orders
            ]);
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
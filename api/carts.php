<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/Cart.php');

$database = new Database();
$db = $database->getConnection();
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
    if ($method === 'GET') {
        // Get user's cart
        $cart->user_id = $user_id;
        $stmt = $cart->getByUser();
        $items = [];
        $total = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item_total = $row['price'] * $row['quantity'];
            $total += $item_total;
            
            $items[] = [
                'id' => (int)$row['id'],
                'product_id' => (int)$row['product_id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'price' => (float)$row['price'],
                'image' => $row['image'],
                'quantity' => (int)$row['quantity'],
                'total' => (float)$item_total,
                'created_at' => $row['created_at']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => (float)$total,
                'count' => count($items)
            ]
        ]);

    } elseif (strpos($request_uri, '/add') !== false && $method === 'POST') {
        // Add item to cart
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->product_id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Product ID is required']);
            exit;
        }

        $cart->user_id = $user_id;
        $cart->product_id = $data->product_id;
        $cart->quantity = $data->quantity ?? 1;

        if($cart->add()) {
            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to add item to cart']);
        }

    } elseif (strpos($request_uri, '/update/') !== false && $method === 'PUT') {
        // Update cart item quantity
        $path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
        $product_id = end($path_parts);
        
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->quantity)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Quantity is required']);
            exit;
        }

        $cart->user_id = $user_id;
        $cart->product_id = $product_id;
        $cart->quantity = $data->quantity;

        if($cart->update()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update cart']);
        }

    } elseif (strpos($request_uri, '/remove/') !== false && $method === 'DELETE') {
        // Remove item from cart
        $path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
        $product_id = end($path_parts);

        $cart->user_id = $user_id;
        $cart->product_id = $product_id;

        if($cart->remove()) {
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to remove item from cart']);
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
<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/Order.php');

try {
    $database = new Database();
    $db = $database->getConnection();
    $order = new Order($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single order
                $order->id = $_GET['id'];
                $result = $order->readOne();
                if($result) {
                    $orderData = [
                        'id' => (int)$order->id,
                        'nft_id' => (int)$order->nft_id,
                        'buyer_id' => (int)$order->buyer_id,
                        'seller_id' => (int)$order->seller_id,
                        'price' => (float)$order->price,
                        'status' => $order->status,
                        'created_at' => $order->created_at
                    ];
                    
                    if ($result['nft_title']) {
                        $orderData['nft'] = [
                            'id' => (int)$result['nft_id'],
                            'title' => $result['nft_title'],
                            'description' => $result['nft_description'],
                            'image_url' => $result['nft_image']
                        ];
                    }
                    
                    if ($result['buyer_name']) {
                        $orderData['buyer'] = [
                            'id' => (int)$result['buyer_id'],
                            'name' => $result['buyer_name'],
                            'email' => $result['buyer_email']
                        ];
                    }
                    
                    if ($result['seller_name']) {
                        $orderData['seller'] = [
                            'id' => (int)$result['seller_id'],
                            'name' => $result['seller_name'],
                            'email' => $result['seller_email']
                        ];
                    }
                    
                    echo json_encode(['success' => true, 'data' => $orderData]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Order not found']);
                }
            } else {
                // Get all orders
                $stmt = $order->readAll();
                $orders = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $orderData = [
                        'id' => (int)$row['id'],
                        'nft_id' => (int)$row['nft_id'],
                        'buyer_id' => (int)$row['buyer_id'],
                        'seller_id' => (int)$row['seller_id'],
                        'price' => (float)$row['price'],
                        'status' => $row['status'],
                        'created_at' => $row['created_at']
                    ];
                    
                    if ($row['nft_title']) {
                        $orderData['nft'] = [
                            'id' => (int)$row['nft_id'],
                            'title' => $row['nft_title'],
                            'description' => $row['nft_description'],
                            'image_url' => $row['nft_image']
                        ];
                    }
                    
                    if ($row['buyer_name']) {
                        $orderData['buyer'] = [
                            'id' => (int)$row['buyer_id'],
                            'name' => $row['buyer_name'],
                            'email' => $row['buyer_email']
                        ];
                    }
                    
                    if ($row['seller_name']) {
                        $orderData['seller'] = [
                            'id' => (int)$row['seller_id'],
                            'name' => $row['seller_name'],
                            'email' => $row['seller_email']
                        ];
                    }
                    
                    $orders[] = $orderData;
                }
                echo json_encode(['success' => true, 'data' => $orders]);
            }
            break;

        case 'POST':
            // Create new order
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->nft_id) || empty($data->buyer_id) || empty($data->seller_id) || empty($data->price)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'NFT ID, buyer ID, seller ID, and price are required']);
                exit;
            }

            $order->nft_id = $data->nft_id;
            $order->buyer_id = $data->buyer_id;
            $order->seller_id = $data->seller_id;
            $order->price = $data->price;
            $order->status = isset($data->status) ? $data->status : 'pending';

            if($order->create()) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$db->lastInsertId(),
                        'nft_id' => (int)$order->nft_id,
                        'buyer_id' => (int)$order->buyer_id,
                        'seller_id' => (int)$order->seller_id,
                        'price' => (float)$order->price,
                        'status' => $order->status,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create order']);
            }
            break;

        case 'PUT':
            // Update order
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Order ID is required']);
                exit;
            }

            $order->id = $data->id;
            $order->status = isset($data->status) ? $data->status : '';
            $order->price = isset($data->price) ? $data->price : 0;

            if($order->update()) {
                $result = $order->readOne();
                if($result) {
                    $orderData = [
                        'id' => (int)$order->id,
                        'nft_id' => (int)$order->nft_id,
                        'buyer_id' => (int)$order->buyer_id,
                        'seller_id' => (int)$order->seller_id,
                        'price' => (float)$order->price,
                        'status' => $order->status,
                        'created_at' => $order->created_at
                    ];
                    
                    if ($result['nft_title']) {
                        $orderData['nft'] = [
                            'id' => (int)$result['nft_id'],
                            'title' => $result['nft_title'],
                            'description' => $result['nft_description'],
                            'image_url' => $result['nft_image']
                        ];
                    }
                    
                    if ($result['buyer_name']) {
                        $orderData['buyer'] = [
                            'id' => (int)$result['buyer_id'],
                            'name' => $result['buyer_name'],
                            'email' => $result['buyer_email']
                        ];
                    }
                    
                    if ($result['seller_name']) {
                        $orderData['seller'] = [
                            'id' => (int)$result['seller_id'],
                            'name' => $result['seller_name'],
                            'email' => $result['seller_email']
                        ];
                    }
                    
                    echo json_encode(['success' => true, 'data' => $orderData]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update order']);
            }
            break;

        case 'DELETE':
            // Delete order
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Order ID is required']);
                exit;
            }

            $order->id = $data->id;
            if($order->delete()) {
                echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete order']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/Transaction.php');

$database = new Database();
$db = $database->getConnection();
$transaction = new Transaction($db);

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
    if (strpos($request_uri, '/me') !== false && $method === 'GET') {
        // Get user's transactions
        $transaction->buyer_id = $user_id;
        $stmt = $transaction->getByUser();
        $transactions = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $transactions[] = [
                'id' => (int)$row['id'],
                'product_id' => (int)$row['product_id'],
                'buyer_id' => (int)$row['buyer_id'],
                'seller_id' => (int)$row['seller_id'],
                'price' => (float)$row['price'],
                'created_at' => $row['created_at'],
                'product' => [
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'image' => $row['image']
                ],
                'buyer_name' => $row['buyer_name'],
                'seller_name' => $row['seller_name'],
                'type' => $row['buyer_id'] == $user_id ? 'purchase' : 'sale'
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $transactions
        ]);

    } elseif (strpos($request_uri, '/transfer') !== false && $method === 'POST') {
        // Create new transaction (NFT transfer)
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->product_id) || empty($data->seller_id) || empty($data->price)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Product ID, seller ID, and price are required']);
            exit;
        }

        $transaction->product_id = $data->product_id;
        $transaction->buyer_id = $user_id;
        $transaction->seller_id = $data->seller_id;
        $transaction->price = $data->price;

        if($transaction->create()) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => (int)$db->lastInsertId(),
                    'product_id' => (int)$transaction->product_id,
                    'buyer_id' => (int)$transaction->buyer_id,
                    'seller_id' => (int)$transaction->seller_id,
                    'price' => (float)$transaction->price
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create transaction']);
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
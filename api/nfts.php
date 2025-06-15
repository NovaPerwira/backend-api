<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../classes/Database.php';
require_once '../classes/NFT.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $nft = new NFT($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single NFT
                $nft->id = $_GET['id'];
                $result = $nft->readOne();
                if($result) {
                    $nftData = [
                        'id' => (int)$nft->id,
                        'title' => $nft->title,
                        'description' => $nft->description,
                        'price' => (float)$nft->price,
                        'image_url' => $nft->image_url,
                        'category_id' => (int)$nft->category_id,
                        'user_id' => (int)$nft->user_id,
                        'status' => $nft->status,
                        'created_at' => $nft->created_at
                    ];
                    
                    if ($result['category_name']) {
                        $nftData['category'] = [
                            'id' => (int)$result['category_id'],
                            'name' => $result['category_name'],
                            'description' => $result['category_description']
                        ];
                    }
                    
                    if ($result['user_name']) {
                        $nftData['user'] = [
                            'id' => (int)$result['user_id'],
                            'name' => $result['user_name'],
                            'email' => $result['user_email']
                        ];
                    }
                    
                    echo json_encode(['success' => true, 'data' => $nftData]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'NFT not found']);
                }
            } else {
                // Get all NFTs
                $stmt = $nft->readAll();
                $nfts = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $nftData = [
                        'id' => (int)$row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'price' => (float)$row['price'],
                        'image_url' => $row['image_url'],
                        'category_id' => (int)$row['category_id'],
                        'user_id' => (int)$row['user_id'],
                        'status' => $row['status'],
                        'created_at' => $row['created_at']
                    ];
                    
                    if ($row['category_name']) {
                        $nftData['category'] = [
                            'id' => (int)$row['category_id'],
                            'name' => $row['category_name'],
                            'description' => $row['category_description']
                        ];
                    }
                    
                    if ($row['user_name']) {
                        $nftData['user'] = [
                            'id' => (int)$row['user_id'],
                            'name' => $row['user_name'],
                            'email' => $row['user_email']
                        ];
                    }
                    
                    $nfts[] = $nftData;
                }
                echo json_encode(['success' => true, 'data' => $nfts]);
            }
            break;

        case 'POST':
            // Create new NFT
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->title) || empty($data->price) || empty($data->user_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Title, price, and user_id are required']);
                exit;
            }

            $nft->title = $data->title;
            $nft->description = isset($data->description) ? $data->description : '';
            $nft->price = $data->price;
            $nft->image_url = isset($data->image_url) ? $data->image_url : '';
            $nft->category_id = isset($data->category_id) ? $data->category_id : null;
            $nft->user_id = $data->user_id;
            $nft->status = isset($data->status) ? $data->status : 'active';

            if($nft->create()) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$db->lastInsertId(),
                        'title' => $nft->title,
                        'description' => $nft->description,
                        'price' => (float)$nft->price,
                        'image_url' => $nft->image_url,
                        'category_id' => $nft->category_id ? (int)$nft->category_id : null,
                        'user_id' => (int)$nft->user_id,
                        'status' => $nft->status,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create NFT']);
            }
            break;

        case 'PUT':
            // Update NFT
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'NFT ID is required']);
                exit;
            }

            $nft->id = $data->id;
            $nft->title = isset($data->title) ? $data->title : '';
            $nft->description = isset($data->description) ? $data->description : '';
            $nft->price = isset($data->price) ? $data->price : 0;
            $nft->image_url = isset($data->image_url) ? $data->image_url : '';
            $nft->category_id = isset($data->category_id) ? $data->category_id : null;
            $nft->status = isset($data->status) ? $data->status : '';

            if($nft->update()) {
                $result = $nft->readOne();
                if($result) {
                    $nftData = [
                        'id' => (int)$nft->id,
                        'title' => $nft->title,
                        'description' => $nft->description,
                        'price' => (float)$nft->price,
                        'image_url' => $nft->image_url,
                        'category_id' => (int)$nft->category_id,
                        'user_id' => (int)$nft->user_id,
                        'status' => $nft->status,
                        'created_at' => $nft->created_at
                    ];
                    
                    if ($result['category_name']) {
                        $nftData['category'] = [
                            'id' => (int)$result['category_id'],
                            'name' => $result['category_name'],
                            'description' => $result['category_description']
                        ];
                    }
                    
                    if ($result['user_name']) {
                        $nftData['user'] = [
                            'id' => (int)$result['user_id'],
                            'name' => $result['user_name'],
                            'email' => $result['user_email']
                        ];
                    }
                    
                    echo json_encode(['success' => true, 'data' => $nftData]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update NFT']);
            }
            break;

        case 'DELETE':
            // Delete NFT
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'NFT ID is required']);
                exit;
            }

            $nft->id = $data->id;
            if($nft->delete()) {
                echo json_encode(['success' => true, 'message' => 'NFT deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete NFT']);
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
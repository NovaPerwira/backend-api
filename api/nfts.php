<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/NFT.php');

try {
    $database = new Database();
    $db = $database->getConnection();
    $nft = new NFT($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $nft->id = $_GET['id'];
                if ($nft->readOneById()) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => (int)$nft->id,
                            'title' => $nft->title,
                            'price' => $nft->price,
                            'image' => $nft->image,
                            'category_id' => (int)$nft->category_id,
                            'description' => $nft->description
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'NFT not found']);
                }
            } else {
                $stmt = $nft->readAll();
                $nfts = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $nfts[] = [
                        'id' => (int)$row['id'],
                        'title' => $row['title'],
                        'price' => $row['price'],
                        'image' => $row['image'],
                        'category_id' => (int)$row['category_id'],
                        $description = $row['description'] ?? null
                    ];
                }
                echo json_encode([
                    'success' => true,
                    'data' => $nfts
                ]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->title)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Title is required']);
                exit;
            }

            $nft->title = $data->title;
            $nft->price = $data->price ?? 0;
            $nft->image = $data->image ?? '';
            $nft->category_id = $data->category_id ?? null;
            $nft->description = $data->description ?? '';

            if ($nft->create()) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$db->lastInsertId(),
                        'title' => $nft->title,
                        'price' => $nft->price,
                        'image' => $nft->image,
                        'category_id' => (int)$nft->category_id,
                        'description' => $nft->description
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create NFT']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'NFT ID is required']);
                exit;
            }

            $nft->id = $data->id;
            $nft->title = $data->title ?? '';
            $nft->price = $data->price ?? 0;
            $nft->image = $data->image ?? '';
            $nft->category_id = $data->category_id ?? null;
            $nft->description = $data->description ?? '';

            if ($nft->updateById()) {
                echo json_encode(['success' => true, 'message' => 'NFT updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update NFT']);
            }
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'NFT ID is required']);
                exit;
            }

            $nft->id = $data->id;
            if ($nft->deleteById()) {
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

<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/Product.php');

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Helper function to generate slug
function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

try {
    if ($method === 'GET') {
        // Check if requesting single product by slug
        $path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
        $slug = end($path_parts);
        
        if ($slug && $slug !== 'products') {
            // Get single product by slug
            $product->slug = $slug;
            $result = $product->readOneBySlug();
            if($result) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'category_id' => (int)$product->category_id,
                        'price' => (float)$product->price,
                        'description' => $product->description,
                        'image' => $product->image,
                        'is_nft' => (bool)$product->is_nft,
                        'created_at' => $product->created_at,
                        'category_name' => $result['category_name']
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Product not found']);
            }
        } else {
            // Get all products with pagination
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;

            $stmt = $product->readAll($limit, $offset);
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'category_id' => (int)$row['category_id'],
                    'price' => (float)$row['price'],
                    'description' => $row['description'],
                    'image' => $row['image'],
                    'is_nft' => (bool)$row['is_nft'],
                    'created_at' => $row['created_at'],
                    'category_name' => $row['category_name']
                ];
            }

            $response = [
                'success' => true,
                'data' => $products
            ];

            // Add pagination info if limit is set
            if ($limit > 0) {
                $total = $product->count();
                $response['pagination'] = [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => (int)$total,
                    'pages' => ceil($total / $limit)
                ];
            }

            echo json_encode($response);
        }

    } elseif ($method === 'POST') {
        // Create new product (admin only)
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->title)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Product title is required']);
            exit;
        }

        $product->title = $data->title;
        $product->slug = isset($data->slug) ? $data->slug : generateSlug($data->title);
        $product->category_id = $data->category_id ?? null;
        $product->price = $data->price ?? 0;
        $product->description = $data->description ?? '';
        $product->image = $data->image ?? '';
        $product->is_nft = $data->is_nft ?? false;

        // Check if slug exists
        if($product->slugExists()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Product slug already exists']);
            exit;
        }

        if($product->create()) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => (int)$db->lastInsertId(),
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'category_id' => (int)$product->category_id,
                    'price' => (float)$product->price,
                    'description' => $product->description,
                    'image' => $product->image,
                    'is_nft' => (bool)$product->is_nft
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create product']);
        }

    } elseif ($method === 'PUT') {
        // Update product
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Product ID is required']);
            exit;
        }

        $product->id = $data->id;
        $product->title = $data->title ?? '';
        $product->slug = $data->slug ?? '';
        $product->category_id = $data->category_id ?? null;
        $product->price = $data->price ?? 0;
        $product->description = $data->description ?? '';
        $product->image = $data->image ?? '';
        $product->is_nft = $data->is_nft ?? false;

        if($product->update()) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update product']);
        }

    } elseif ($method === 'DELETE') {
        // Delete product
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Product ID is required']);
            exit;
        }

        $product->id = $data->id;
        if($product->delete()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete product']);
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
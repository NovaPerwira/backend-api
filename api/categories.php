<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/Category.php');

try {
    $database = new Database();
    $db = $database->getConnection();
    $category = new Category($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['slug'])) {
                // Get single category by slug
                $category->slug = $_GET['slug'];
                if($category->readOneBySlug()) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => (int)$category->id,
                            'slug' => $category->slug,
                            'title' => $category->title,
                            'description' => $category->description,
                            'thumbnail' => $category->thumbnail
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Category not found']);
                }
            } else {
                // Get all categories with pagination
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                $stmt = $category->readAll($limit, $offset);
                $categories = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $categories[] = [
                        'id' => (int)$row['id'],
                        'slug' => $row['slug'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'thumbnail' => $row['thumbnail']
                    ];
                }

                $response = [
                    'success' => true,
                    'data' => $categories
                ];

                // Add pagination info if limit is set
                if ($limit > 0) {
                    $total = $category->count();
                    $response['pagination'] = [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => (int)$total,
                        'pages' => ceil($total / $limit)
                    ];
                }

                echo json_encode($response);
            }
            break;

        case 'POST':
            // Create new category
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->title)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category title is required']);
                exit;
            }

            if (empty($data->slug)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category slug is required']);
                exit;
            }

            $category->slug = $data->slug;
            $category->title = $data->title;
            $category->description = isset($data->description) ? $data->description : '';
            $category->thumbnail = isset($data->thumbnail) ? $data->thumbnail : '';

            if($category->create()) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$db->lastInsertId(),
                        'slug' => $data->slug,
                        'title' => $data->title,
                        'description' => $category->description,
                        'thumbnail' => $category->thumbnail
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create category']);
            }
            break;

        case 'PUT':
            // Update category by slug
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->slug)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category slug is required']);
                exit;
            }

            $category->slug = $data->slug;
            $category->title = isset($data->title) ? $data->title : '';
            $category->description = isset($data->description) ? $data->description : '';
            $category->thumbnail = isset($data->thumbnail) ? $data->thumbnail : '';

            if($category->updateBySlug()) {
                if($category->readOneBySlug()) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => (int)$category->id,
                            'slug' => $category->slug,
                            'title' => $category->title,
                            'description' => $category->description,
                            'thumbnail' => $category->thumbnail
                        ]
                    ]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update category']);
            }
            break;

        case 'DELETE':
            // Delete category by slug
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->slug)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category slug is required']);
                exit;
            }

            $category->slug = $data->slug;
            if($category->deleteBySlug()) {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete category']);
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
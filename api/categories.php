<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
require_once('C:\laragon\www\php\api../config/database.php');
require_once __DIR__ . '/../classes/Category.php';



try {
    $database = new Database();
    $db = $database->getConnection();
    $category = new Category($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single category
                $category->id = $_GET['id'];
                if($category->readOne()) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => (int)$category->id,
                            'name' => $category->name,
                            'description' => $category->description,
                            'created_at' => $category->created_at
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Category not found']);
                }
            } else {
                // Get all categories
                $stmt = $category->readAll();
                $categories = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $categories[] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'created_at' => $row['created_at']
                    ];
                }
                echo json_encode(['success' => true, 'data' => $categories]);
            }
            break;

        case 'POST':
            // Create new category
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category name is required']);
                exit;
            }

            $category->name = $data->name;
            $category->description = isset($data->description) ? $data->description : '';

            // Check if category name exists
            if($category->nameExists()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Category name already exists']);
                exit;
            }

            if($category->create()) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$db->lastInsertId(),
                        'name' => $data->name,
                        'description' => $category->description,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create category']);
            }
            break;

        case 'PUT':
            // Update category
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category ID is required']);
                exit;
            }

            $category->id = $data->id;
            $category->name = isset($data->name) ? $data->name : '';
            $category->description = isset($data->description) ? $data->description : '';

            if($category->update()) {
                if($category->readOne()) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => (int)$category->id,
                            'name' => $category->name,
                            'description' => $category->description,
                            'created_at' => $category->created_at
                        ]
                    ]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update category']);
            }
            break;

        case 'DELETE':
            // Delete category
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category ID is required']);
                exit;
            }

            $category->id = $data->id;
            if($category->delete()) {
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
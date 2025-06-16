<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/Category.php');

$database = new Database();
$db = $database->getConnection();
$category = new Category($db);

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get all categories
        $stmt = $category->readAll();
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'slug' => $row['slug']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);

    } elseif ($method === 'POST') {
        // Create new category (admin only)
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name) || empty($data->slug)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Category name and slug are required']);
            exit;
        }

        $category->name = $data->name;
        $category->slug = $data->slug;

        if($category->create()) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => (int)$db->lastInsertId(),
                    'name' => $category->name,
                    'slug' => $category->slug
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create category']);
        }

    } elseif ($method === 'DELETE') {
        // Delete category
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }

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

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
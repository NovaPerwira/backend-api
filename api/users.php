<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/User    .php');

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single user
                $user->id = $_GET['id'];
                if($user->readOne()) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => (int)$user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'created_at' => $user->created_at,
                            'last_login' => $user->last_login
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'User not found']);
                }
            } else {
                // Get all users
                $stmt = $user->readAll();
                $users = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $users[] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'role' => $row['role'],
                        'created_at' => $row['created_at'],
                        'last_login' => $row['last_login']
                    ];
                }
                echo json_encode(['success' => true, 'data' => $users]);
            }
            break;

        case 'POST':
            // Create new user
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->name) || empty($data->email) || empty($data->password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Name, email, and password are required']);
                exit;
            }

            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = $data->password;
            $user->role = isset($data->role) ? $data->role : 'user';

            // Check if email exists
            if($user->emailExists()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Email already exists']);
                exit;
            }

            if($user->create()) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$db->lastInsertId(),
                        'name' => $data->name,
                        'email' => $data->email,
                        'role' => $user->role,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create user']);
            }
            break;

        case 'PUT':
            // Update user
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            $user->id = $data->id;
            $user->name = isset($data->name) ? $data->name : '';
            $user->email = isset($data->email) ? $data->email : '';
            $user->role = isset($data->role) ? $data->role : '';
            $user->password = isset($data->password) ? $data->password : '';

            if($user->update()) {
                if($user->readOne()) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => (int)$user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'created_at' => $user->created_at,
                            'last_login' => $user->last_login
                        ]
                    ]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update user']);
            }
            break;

        case 'DELETE':
            // Delete user
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            $user->id = $data->id;
            if($user->delete()) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
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
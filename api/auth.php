<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../classes/User.php');

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

try {
    if (strpos($request_uri, '/register') !== false && $method === 'POST') {
        // Register endpoint
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name) || empty($data->email) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Name, email and password are required']);
            exit;
        }

        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = $data->password;

        // Check if email exists
        if($user->emailExists()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            exit;
        }

        if($user->create()) {
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => (int)$db->lastInsertId(),
                    'name' => $data->name,
                    'email' => $data->email
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to register user']);
        }
        
    } elseif (strpos($request_uri, '/login') !== false && $method === 'POST') {
        // Login endpoint
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->email) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and password are required']);
            exit;
        }

        $user->email = $data->email;
        $user->password = $data->password;

        if($user->login()) {
            // Start session
            session_start();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;

            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => (int)$user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        }
        
    } elseif (strpos($request_uri, '/me') !== false && $method === 'GET') {
        // Get current user endpoint
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $user->id = $_SESSION['user_id'];
        if($user->readOne()) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => (int)$user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
        
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
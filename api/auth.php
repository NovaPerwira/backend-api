<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');
require_once '../classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

try {
    if (strpos($request_uri, '/login') !== false && $method === 'POST') {
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
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        }
        
    } elseif (strpos($request_uri, '/logout') !== false && $method === 'POST') {
        // Logout endpoint
        session_start();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
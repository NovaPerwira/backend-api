<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/../config/database.php');

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get total users
    $userQuery = "SELECT COUNT(*) as total FROM users";
    $userStmt = $db->prepare($userQuery);
    $userStmt->execute();
    $totalUsers = $userStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total products
    $productQuery = "SELECT COUNT(*) as total FROM products";
    $productStmt = $db->prepare($productQuery);
    $productStmt->execute();
    $totalProducts = $productStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total orders
    $orderQuery = "SELECT COUNT(*) as total FROM orders";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->execute();
    $totalOrders = $orderStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total revenue
    $revenueQuery = "SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE status = 'paid'";
    $revenueStmt = $db->prepare($revenueQuery);
    $revenueStmt->execute();
    $totalRevenue = $revenueStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get recent activity
    $activityQuery = "
        SELECT 'user' as type, CONCAT('New user: ', name) as title, 
               CONCAT('User ', name, ' joined the platform') as description,
               created_at as time, name as user
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
        UNION ALL
        SELECT 'product' as type, CONCAT('New product: ', title) as title,
               CONCAT('Product \"', title, '\" was created') as description,
               created_at as time, 'System' as user
        FROM products 
        ORDER BY created_at DESC 
        LIMIT 5
        UNION ALL
        SELECT 'order' as type, CONCAT('New order #', id) as title,
               CONCAT('Order for $', total_price, ' was placed') as description,
               created_at as time, 'System' as user
        FROM orders 
        ORDER BY created_at DESC 
        LIMIT 5
        ORDER BY time DESC
        LIMIT 10
    ";
    $activityStmt = $db->prepare($activityQuery);
    $activityStmt->execute();
    $recentActivity = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'data' => [
            'totalUsers' => (int)$totalUsers,
            'totalProducts' => (int)$totalProducts,
            'totalOrders' => (int)$totalOrders,
            'totalRevenue' => (float)$totalRevenue,
            'recentActivity' => $recentActivity
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
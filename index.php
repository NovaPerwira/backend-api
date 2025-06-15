<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'classes/User.php';
require_once 'classes/Category.php';
require_once 'classes/NFT.php';
require_once 'classes/Order.php';

require_login();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$category = new Category($db);
$nft = new NFT($db);
$order = new Order($db);

// Get statistics
$total_users = $user->count();
$total_categories = $category->count();
$total_nfts = $nft->count();
$total_orders = $order->count();
$total_revenue = $order->getTotalRevenue();

// Get recent orders
$recent_orders = $order->read(5, 0);

// Get revenue data for chart
$revenue_data = $order->getRevenueByMonth();
$revenue_chart_data = [];
while ($row = $revenue_data->fetch(PDO::FETCH_ASSOC)) {
    $revenue_chart_data[] = $row;
}

// Get order status stats
$status_data = $order->getStatusStats();
$status_chart_data = [];
while ($row = $status_data->fetch(PDO::FETCH_ASSOC)) {
    $status_chart_data[] = $row;
}

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
        <?php include 'includes/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto p-6">
            <div class="space-y-6 fade-in">
                <!-- Header -->
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-2">Welcome back! Here's what's happening with your NFT marketplace.</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover-scale hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo number_format($total_users); ?></p>
                                <div class="flex items-center mt-2">
                                    <i class="fas fa-arrow-up text-green-500 text-sm mr-1"></i>
                                    <span class="text-sm font-medium text-green-600">+12%</span>
                                    <span class="text-sm text-gray-500 ml-1">vs last month</span>
                                </div>
                            </div>
                            <div class="bg-blue-500 p-3 rounded-lg">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover-scale hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Orders</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo number_format($total_orders); ?></p>
                                <div class="flex items-center mt-2">
                                    <i class="fas fa-arrow-up text-green-500 text-sm mr-1"></i>
                                    <span class="text-sm font-medium text-green-600">+8%</span>
                                    <span class="text-sm text-gray-500 ml-1">vs last month</span>
                                </div>
                            </div>
                            <div class="bg-green-500 p-3 rounded-lg">
                                <i class="fas fa-shopping-cart text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover-scale hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo format_currency($total_revenue); ?></p>
                                <div class="flex items-center mt-2">
                                    <i class="fas fa-arrow-up text-green-500 text-sm mr-1"></i>
                                    <span class="text-sm font-medium text-green-600">+15%</span>
                                    <span class="text-sm text-gray-500 ml-1">vs last month</span>
                                </div>
                            </div>
                            <div class="bg-purple-500 p-3 rounded-lg">
                                <i class="fas fa-dollar-sign text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover-scale hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total NFTs</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo number_format($total_nfts); ?></p>
                                <div class="flex items-center mt-2">
                                    <i class="fas fa-arrow-down text-red-500 text-sm mr-1"></i>
                                    <span class="text-sm font-medium text-red-600">-2%</span>
                                    <span class="text-sm text-gray-500 ml-1">vs last month</span>
                                </div>
                            </div>
                            <div class="bg-orange-500 p-3 rounded-lg">
                                <i class="fas fa-image text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Revenue Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h3>
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Order Status Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Status Distribution</h3>
                        <canvas id="statusChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                            <a href="orders.php" class="text-blue-600 hover:text-blue-500 text-sm font-medium">View all</a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $recent_orders->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $row['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['user_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['user_email']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo format_currency($row['total_price']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $color_class = $status_colors[$row['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $color_class; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo format_date($row['created_at']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($revenue_chart_data, 'month')); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode(array_column($revenue_chart_data, 'revenue')); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($status_chart_data, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($status_chart_data, 'count')); ?>,
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
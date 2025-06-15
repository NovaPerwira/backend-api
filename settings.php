<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

require_login();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                // In a real application, you would update the admin profile in database
                $_SESSION['admin_name'] = sanitize_input($_POST['name']);
                $_SESSION['admin_email'] = sanitize_input($_POST['email']);
                set_flash_message('Profile updated successfully!', 'success');
                break;
                
            case 'change_password':
                // In a real application, you would verify current password and update
                if ($_POST['new_password'] === $_POST['confirm_password']) {
                    set_flash_message('Password changed successfully!', 'success');
                } else {
                    set_flash_message('Passwords do not match!', 'error');
                }
                break;
        }
        redirect('settings.php');
    }
}

$page_title = 'Settings';
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
                    <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
                    <p class="text-gray-600 mt-2">Manage your account settings and preferences</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Profile Settings -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Profile Information</h3>
                        </div>
                        <form method="POST" class="p-6 space-y-4">
                            <input type="hidden" name="action" value="update_profile">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['admin_email']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div class="pt-4">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Password Settings -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
                        </div>
                        <form method="POST" class="p-6 space-y-4">
                            <input type="hidden" name="action" value="change_password">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                <input type="password" name="current_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                <input type="password" name="new_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div class="pt-4">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- System Settings -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">System Settings</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                                    <p class="text-sm text-gray-500">Receive email notifications for new orders</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Auto Backup</h4>
                                    <p class="text-sm text-gray-500">Automatically backup database daily</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Maintenance Mode</h4>
                                    <p class="text-sm text-gray-500">Put the site in maintenance mode</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Database Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Database Information</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Database Host:</span>
                                <span class="text-sm text-gray-900"><?php echo DB_HOST; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Database Name:</span>
                                <span class="text-sm text-gray-900"><?php echo DB_NAME; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">PHP Version:</span>
                                <span class="text-sm text-gray-900"><?php echo phpversion(); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Server Time:</span>
                                <span class="text-sm text-gray-900"><?php echo date('Y-m-d H:i:s'); ?></span>
                            </div>
                            <div class="pt-4">
                                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    <i class="fas fa-download mr-2"></i>Backup Database
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
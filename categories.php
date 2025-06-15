<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'classes/Category.php';

require_login();

$database = new Database();
$db = $database->getConnection();
$category = new Category($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
    case 'create':
    $category->slug = generate_slug($_POST['slug']);
    $category->title = sanitize_input($_POST['title']);
    $category->description = sanitize_input($_POST['description']);
    $category->thumbnail = sanitize_input($_POST['thumbnail']);

    if ($category->existsBySlug()) {
        set_flash_message('Slug already exists! Please use a different one.', 'error');
    } else {
        if ($category->create()) {
            set_flash_message('Category created successfully!', 'success');
        } else {
            set_flash_message('Failed to create category.', 'error');
        }
    }
    break;

    


    case 'update':
        $category->slug = sanitize_input($_POST['slug']); // pakai slug, bukan id
        $category->title = sanitize_input($_POST['title']);
        $category->description = sanitize_input($_POST['description']);
        $category->thumbnail = sanitize_input($_POST['thumbnail']);

        if ($category->updateBySlug()) {
            set_flash_message('Category updated successfully!', 'success');
        } else {
            set_flash_message('Failed to update category.', 'error');
        }
        break;

    case 'delete':
        $category->slug = sanitize_input($_POST['slug']); // pakai slug, bukan id
        if ($category->deleteBySlug()) {
            set_flash_message('Category deleted successfully!', 'success');
        } else {
            set_flash_message('Failed to delete category.', 'error');
        }
        break;
}

        redirect('categories.php');
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = RECORDS_PER_PAGE;
$offset = ($page - 1) * $records_per_page;

$stmt = $category->readAll($records_per_page, $offset);
$total_records = $category->count();
$total_pages = ceil($total_records / $records_per_page);

$page_title = 'Categories Management';
include 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
        <?php include 'includes/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto p-6">
            <div class="space-y-6 fade-in">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Categories Management</h1>
                        <p class="text-gray-600 mt-2">Manage your NFT categories</p>
                    </div>
                    <button onclick="openModal('createModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Category
                    </button>
                </div>

                <!-- Categories Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover-scale hover:shadow-md transition-all duration-200">
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="<?php echo htmlspecialchars($row['thumbnail'] ?: 'https://images.pexels.com/photos/1183992/pexels-photo-1183992.jpeg'); ?>" 
                                 alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                 class="w-full h-48 object-cover">
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($row['description'] ?: 'No description available'); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($row['slug']); ?>
                                </span>
                                <div class="flex space-x-2">
                                    <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                            class="text-blue-600 hover:text-blue-900 p-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteCategory('<?php echo $row['slug']; ?>', '<?php echo htmlspecialchars($row['title']); ?>')" 
                                            class="text-red-600 hover:text-red-900 p-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="flex items-center justify-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" 
                           class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="px-3 py-2 text-sm <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> rounded-lg">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" 
                           class="px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
<!-- Create Category Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="closeModal('createModal')"></div>
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full z-50">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Add New Category</h3>
            </div>
            <form method="POST" action="categories.php" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create">

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Slug as Radio -->
                <!-- Slug as Dropdown -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Category Type (Slug)</label>
    <select name="slug" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <option value="">-- Select Category Type --</option>
        <option value="Wearables">Wearables</option>
        <option value="Accessories">Accessories</option>
        <option value="gaming">Gaming</option>
        <option value="photography">Photography</option>
        <option value="collectibles">Collectibles</option>
    </select>
</div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>

                <!-- Thumbnail -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Thumbnail URL</label>
                    <input type="url" name="thumbnail" placeholder="https://example.com/image.jpg"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal('createModal')"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Category Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="closeModal('editModal')"></div>
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full z-50">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Edit Category</h3>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="slug" id="edit_slug">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" id="edit_title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Thumbnail URL</label>
                    <input type="url" name="thumbnail" id="edit_thumbnail" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="closeModal('deleteModal')"></div>
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full z-50">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Confirm Delete</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Are you sure you want to delete <span id="delete_category_name" class="font-semibold"></span>? This action cannot be undone.</p>
                <form method="POST" class="flex justify-end space-x-3">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="slug" id="delete_slug">
                    <button type="button" onclick="closeModal('deleteModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function editCategory(category) {
    document.getElementById('edit_slug').value = category.slug;
    document.getElementById('edit_title').value = category.title;
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_thumbnail').value = category.thumbnail || '';
    openModal('editModal');
}

function deleteCategory(slug, title) {
    document.getElementById('delete_slug').value = slug;
    document.getElementById('delete_category_name').textContent = title;
    openModal('deleteModal');
}
</script>

<?php include 'includes/footer.php'; ?>
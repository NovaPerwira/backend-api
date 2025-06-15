    <?php
    require_once 'config/config.php';
    require_once 'config/database.php';
    require_once 'includes/functions.php';
    require_once 'classes/NFT.php';
    require_once 'classes/Category.php';

    require_login();

    $database = new Database();
    $db = $database->getConnection();
    $nft = new NFT($db);
    $category = new Category($db);

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $nft->title = sanitize_input($_POST['name']);
                    $nft->price = (float)$_POST['price'];
                    $nft->image = sanitize_input($_POST['image']);
                    $nft->category_id = $_POST['category_id'] ?: null;
                    $nft->description = sanitize_input($_POST['description']);
                    
                    if ($nft->create()) {
                        set_flash_message('NFT created successfully!', 'success');
                    } else {
                        set_flash_message('Failed to create NFT.', 'error');
                    }
                    break;
                    
                case 'update':
                    $nft->id = $_POST['id'];
                    $nft->title = sanitize_input($_POST['title']);
                    $nft->price = (float)$_POST['price'];
                    $nft->image = sanitize_input($_POST['image']);
                    $nft->category_id = $_POST['category_id'] ?: null;
                    $nft->description = sanitize_input($_POST['description']);
                    
                    if ($nft->update()) {
                        set_flash_message('NFT updated successfully!', 'success');
                    } else {
                        set_flash_message('Failed to update NFT.', 'error');
                    }
                    break;
                    
                case 'delete':
                    $nft->id = $_POST['id'];
                    if ($nft->delete()) {
                        set_flash_message('NFT deleted successfully!', 'success');
                    } else {
                        set_flash_message('Failed to delete NFT.', 'error');
                    }
                    break;
            }
            redirect('nfts.php');
        }
    }

    // Get categories for dropdown
    $categories_stmt = $category->readAll();
    $categories = [];
    while ($cat = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $cat;
    }

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = RECORDS_PER_PAGE;
    $offset = ($page - 1) * $records_per_page;

    $stmt = $nft->read($records_per_page, $offset);
    $total_records = $nft->count();
    $total_pages = ceil($total_records / $records_per_page);

    $page_title = 'NFTs Management';
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
                            <h1 class="text-3xl font-bold text-gray-900">NFTs Management</h1>
                            <p class="text-gray-600 mt-2">Manage your NFT collection</p>
                        </div>
                        <button onclick="openModal('createModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Add NFT
                        </button>
                    </div>

                    <!-- NFTs Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover-scale hover:shadow-md transition-all duration-200">
                            <div class="aspect-w-1 aspect-h-1">
                                <img src="<?php echo htmlspecialchars($row['image']); ?>" 
                                    alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                    class="w-full h-48 object-cover">
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?php echo htmlspecialchars($row['description'] ?: 'No description available'); ?></p>
                                
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl font-bold text-green-600"><?php echo format_currency($row['price']); ?></span>
                                    <?php if ($row['category_id']): ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            <?php echo htmlspecialchars($row['category_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-between">
                                    <button onclick="editNFT(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                            class="flex-1 mr-2 bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button onclick="deleteNFT(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['title']); ?>')" 
                                            class="flex-1 ml-2 bg-red-50 text-red-600 hover:bg-red-100 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
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

    <!-- Create NFT Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="closeModal('createModal')"></div>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full z-50">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Add New NFT</h3>
                </div>
                <form method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="action" value="create">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price (ETH)</label>
                        <input type="number" name="price" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                        <input type="url" name="image" required placeholder="https://example.com/image.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create NFT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit NFT Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="closeModal('editModal')"></div>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full z-50">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Edit NFT</h3>
                </div>
                <form method="POST" class="p-6 space-y-4">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price (ETH)</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                        <input type="url" name="image" id="edit_image" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category_id" id="edit_category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="edit_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update NFT</button>
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
                    <p class="text-gray-600 mb-4">Are you sure you want to delete <span id="delete_nft_name" class="font-semibold"></span>? This action cannot be undone.</p>
                    <form method="POST" class="flex justify-end space-x-3">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
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

    function editNFT(nft) {
        document.getElementById('edit_id').value = nft.id;
        document.getElementById('edit_name').value = nft.name;
        document.getElementById('edit_price').value = nft.price;
        document.getElementById('edit_image').value = nft.image;
        document.getElementById('edit_category_id').value = nft.category_id || '';
        document.getElementById('edit_description').value = nft.description || '';
        openModal('editModal');
    }

    function deleteNFT(id, name) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_nft_name').textContent = name;
        openModal('deleteModal');
    }
    </script>

    <?php include 'includes/footer.php'; ?>
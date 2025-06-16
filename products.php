<?php
require_once 'config/database.php';
require_once 'classes/Product.php';

// Fungsi bantu
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
function redirect($url) {
    header("Location: $url");
    exit;
}

// Buat koneksi DB
$database = new Database();
$conn = $database->getConnection();

$product = new Product($conn);

// Pastikan ada slug kategori
if (!isset($_GET['slug'])) {
    die("Slug kategori tidak ditemukan.");
}
$slug = sanitize_input($_GET['slug']);

// Tangani form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $product->name = sanitize_input($_POST['name']);
                $product->price = sanitize_input($_POST['price']);
                $product->image = sanitize_input($_POST['image']);
                $product->category_slug = $slug;
                $product->create();
                break;

            case 'update':
                $product->id = $_POST['id'];
                $product->name = sanitize_input($_POST['name']);
                $product->price = sanitize_input($_POST['price']);
                $product->image = sanitize_input($_POST['image']);
                $product->category_slug = $slug;
                $product->updateById();
                break;

            case 'delete':
                $product->id = $_POST['id'];
                $product->deleteById();
                break;
        }

        redirect("products.php?slug=" . $slug);
    }
}

// Ambil produk berdasarkan kategori
$products = $product->getByCategorySlug($slug);
?>

<!-- HTML UI -->
<!DOCTYPE html>
<html>
<head>
    <title>Produk - <?= htmlspecialchars($slug) ?></title>
</head>
<body>
<h1>Produk untuk kategori: <strong><?= htmlspecialchars($slug) ?></strong></h1>

<h2>Tambah Produk</h2>
<form method="POST">
    <input type="hidden" name="action" value="create" />
    <input type="text" name="name" placeholder="Nama Produk" required><br>
    <input type="text" name="price" placeholder="Harga" required><br>
    <input type="text" name="image" placeholder="URL Gambar"><br>
    <button type="submit">Simpan</button>
</form>

<h2>Daftar Produk</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Nama</th>
        <th>Harga</th>
        <th>Gambar</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($products as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['price']) ?></td>
        <td><img src="<?= htmlspecialchars($p['image']) ?>" width="50" /></td>
        <td>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>

<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../uye/giris.php');
    exit;
}

// Ürün ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $productName = $_POST['product_name'];
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $categoryId = (int)$_POST['category_id'];
    $discount = (float)$_POST['discount'];
    
    // Resim yükleme
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/products/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'assets/images/products/' . $fileName;
        }
    }
    
    $stmt = $db->prepare("INSERT INTO Ürünler (ÜrünAdı, Açıklama, Fiyat, StokMiktarı, KategoriID, ResimYolu, İndirimOranı) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$productName, $description, $price, $stock, $categoryId, $imagePath, $discount]);
    
    $_SESSION['success_message'] = 'Ürün başarıyla eklendi!';
    header('Location: urunler.php');
    exit;
}

// Ürün silme
if (isset($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    
    try {
        // Resmi de silmek için önce resim yolunu al
        $stmt = $db->prepare("SELECT ResimYolu FROM Ürünler WHERE ÜrünID = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && $product['ResimYolu']) {
            $imagePath = '../' . $product['ResimYolu'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $db->prepare("DELETE FROM Ürünler WHERE ÜrünID = ?");
        $stmt->execute([$productId]);
        
        $_SESSION['success_message'] = 'Ürün başarıyla silindi!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Ürün silinirken hata oluştu: ' . $e->getMessage();
    }
    
    header('Location: urunler.php');
    exit;
}

// Ürünleri listele
$stmt = $db->query("
    SELECT u.*, k.KategoriAdı 
    FROM Ürünler u
    LEFT JOIN Kategoriler k ON u.KategoriID = k.KategoriID
    ORDER BY u.EklenmeTarihi DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kategorileri al (dropdown için)
$stmt = $db->query("SELECT * FROM Kategoriler");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Ürün Yönetimi</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Ürün Ekle</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Ürün Adı</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Fiyat</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stok Miktarı</label>
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Kategori</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['KategoriID'] ?>"><?= $category['KategoriAdı'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="discount" class="form-label">İndirim Oranı (%)</label>
                                <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Ürün Resmi</label>
                            <input type="file" class="form-control" id="image" name="image">
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary">Ekle</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Ürün Listesi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ürün Adı</th>
                                    <th>Fiyat</th>
                                    <th>Stok</th>
                                    <th>Kategori</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= $product['ÜrünID'] ?></td>
                                        <td><?= $product['ÜrünAdı'] ?></td>
                                        <td><?= number_format($product['Fiyat'], 2) ?> TL</td>
                                        <td><?= $product['StokMiktarı'] ?></td>
                                        <td><?= $product['KategoriAdı'] ?? '-' ?></td>
                                        <td>
                                            <a href="urun_duzenle.php?id=<?= $product['ÜrünID'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <a href="urunler.php?delete=<?= $product['ÜrünID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
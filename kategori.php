<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Kategori bilgilerini al
$stmt = $db->prepare("SELECT * FROM Kategoriler WHERE KategoriID = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: index.php');
    exit;
}

// Alt kategorileri al
$stmt = $db->prepare("SELECT * FROM Kategoriler WHERE ÜstKategoriID = ?");
$stmt->execute([$categoryId]);
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bu kategorideki ürünleri al
$products = getProducts($categoryId);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $category['KategoriAdı'] ?></li>
        </ol>
    </nav>
    
    <h2><?= $category['KategoriAdı'] ?></h2>
    
    <?php if ($category['Açıklama']): ?>
        <p><?= $category['Açıklama'] ?></p>
    <?php endif; ?>
    
    <?php if (!empty($subcategories)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <h4>Alt Kategoriler</h4>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($subcategories as $subcategory): ?>
                        <a href="kategori.php?id=<?= $subcategory['KategoriID'] ?>" class="btn btn-outline-primary">
                            <?= $subcategory['KategoriAdı'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">Bu kategoride henüz ürün bulunmamaktadır.</div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="<?= $product['ResimYolu'] ?: 'assets/images/no-image.jpg' ?>" class="card-img-top" alt="<?= $product['ÜrünAdı'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['ÜrünAdı'] ?></h5>
                            <p class="card-text">
                                <?php if ($product['İndirimOranı'] > 0): ?>
                                    <span class="text-danger"><del><?= number_format($product['Fiyat'], 2) ?> TL</del></span>
                                    <br>
                                    <strong><?= number_format($product['Fiyat'] * (1 - $product['İndirimOranı'] / 100), 2) ?> TL</strong>
                                    <span class="badge bg-success">%<?= $product['İndirimOranı'] ?> indirim</span>
                                <?php else: ?>
                                    <strong><?= number_format($product['Fiyat'], 2) ?> TL</strong>
                                <?php endif; ?>
                            </p>
                            <a href="urun-detay.php?id=<?= $product['ÜrünID'] ?>" class="btn btn-primary">Detay</a>
                            <button class="btn btn-outline-success add-to-cart" data-id="<?= $product['ÜrünID'] ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
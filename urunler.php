<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$products = getProducts($categoryId, $search);

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Ürünler</h2>
    </div>
    <div class="col-md-6">
        <form class="d-flex" method="GET" action="urunler.php">
            <input class="form-control me-2" type="search" name="search" placeholder="Ürün ara..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-success" type="submit">Ara</button>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($products)): ?>
        <div class="col-12">
            <div class="alert alert-info">Ürün bulunamadı.</div>
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

<?php
require_once 'includes/footer.php';
?>
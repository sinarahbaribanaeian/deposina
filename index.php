<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$popularProducts = getProducts(null, null, 8);
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="assets/images/slider1.jpg" class="d-block w-100" alt="Slider 1" />
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/slider2.jpg" class="d-block w-100" alt="Slider 2" />
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </div>

    <h2 class="mb-4">Popüler Ürünler</h2>
    <div class="row">
        <?php foreach ($popularProducts as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="<?= $product['ResimYolu'] ? ltrim($product['ResimYolu'], '/') : 'assets/images/no-image.jpg' ?>"
                         class="card-img-top" alt="<?= htmlspecialchars($product['ÜrünAdı']) ?>" />
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['ÜrünAdı']) ?></h5>
                        <p class="card-text">
                            <?php if ($product['İndirimOranı'] > 0): ?>
                                <span class="text-danger">
                                    <del><?= number_format($product['Fiyat'], 2) ?> TL</del>
                                </span><br>
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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

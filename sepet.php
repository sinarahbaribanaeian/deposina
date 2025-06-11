<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Sepet işlemleri için veritabanı prosedürü oluşturalım (bir kere çalıştırılacak)
/*
CREATE PROCEDURE UpdateCartStock(IN product_id INT, IN quantity_change INT)
BEGIN
    UPDATE Urunler SET StokMiktari = StokMiktari - quantity_change 
    WHERE UrunID = product_id AND StokMiktari >= quantity_change;
END
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $productId => $quantity) {
        $productId = (int)$productId;
        $quantity = (int)$quantity;
        
        // Stok kontrolü yap
        $stmt = $db->prepare("SELECT StokMiktari FROM Urunler WHERE UrunID = ?");
        $stmt->execute([$productId]);
        $stock = $stmt->fetchColumn();
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } elseif ($quantity <= $stock) {
            $_SESSION['cart'][$productId] = $quantity;
        } else {
            $_SESSION['error_message'] = $productId . " ID'li ürün için yeterli stok yok (Maksimum: " . $stock . ")";
        }
    }
    
    // Stok güncelleme trigger'ı tetiklenir (aşağıda trigger tanımı var)
}

if (isset($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header('Location: sepet.php');
    exit;
}

$cartItems = getCartItems();
$cartTotal = getCartTotal();

require_once 'includes/header.php';
?>

<h2 class="mb-4">Alışveriş Sepeti</h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info">Sepetiniz boş.</div>
    <a href="urunler.php" class="btn btn-primary">Alışverişe Devam Et</a>
<?php else: ?>
    <form action="sepet.php" method="post">
        <table class="table">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th>Adet</th>
                    <th>Fiyat</th>
                    <th>Toplam</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= $item['product']['ResimYolu'] ?: 'assets/images/no-image.jpg' ?>" 
                                     alt="<?= htmlspecialchars($item['product']['UrunAdi']) ?>" 
                                     width="50" class="me-3">
                                <div><?= htmlspecialchars($item['product']['UrunAdi']) ?></div>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="quantity[<?= $item['product']['UrunID'] ?>]" 
                                   value="<?= $item['quantity'] ?>" min="1" max="<?= $item['product']['StokMiktari'] ?>" 
                                   class="form-control quantity-input">
                        </td>
                        <td><?= number_format($item['product']['IndirimliFiyat'] ?? $item['product']['Fiyat'], 2) ?> TL</td>
                        <td><?= number_format($item['total'], 2) ?> TL</td>
                        <td>
                            <a href="sepet.php?remove=<?= $item['product']['UrunID'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Bu ürünü sepetinizden çıkarmak istediğinize emin misiniz?')">
                                Kaldır
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Toplam:</strong></td>
                    <td><strong><?= number_format($cartTotal, 2) ?> TL</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <div class="d-flex justify-content-between">
            <a href="urunler.php" class="btn btn-primary">Alışverişe Devam Et</a>
            <div>
                <button type="submit" name="update_cart" class="btn btn-warning me-2">Sepeti Güncelle</button>
                <a href="odeme.php" class="btn btn-success">Ödeme Yap</a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>

<?php

?>
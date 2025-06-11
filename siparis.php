<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: uye/giris.php');
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Sipariş bilgilerini al
$stmt = $db->prepare("
    SELECT s.*, k.Ad, k.Soyad, k.Email, k.Telefon, k.Adres 
    FROM Siparişler s
    JOIN Kullanıcılar k ON s.KullanıcıID = k.KullanıcıID
    WHERE s.SiparişID = ? AND s.KullanıcıID = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: uye/panel.php');
    exit;
}

// Sipariş detaylarını al
$stmt = $db->prepare("
    SELECT sd.*, u.ÜrünAdı, u.ResimYolu 
    FROM SiparişDetayları sd
    JOIN Ürünler u ON sd.ÜrünID = u.ÜrünID
    WHERE sd.SiparişID = ?
");
$stmt->execute([$orderId]);
$orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ödeme bilgilerini al
$stmt = $db->prepare("SELECT * FROM Ödemeler WHERE SiparişID = ?");
$stmt->execute([$orderId]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
            <li class="breadcrumb-item"><a href="uye/panel.php">Hesabım</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sipariş Detayı</li>
        </ol>
    </nav>
    
    <h2>Sipariş Detayı (#<?= $order['SiparişID'] ?>)</h2>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Sipariş Bilgileri</h5>
                </div>
                <div class="card-body">
                    <p><strong>Sipariş Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($order['SiparişTarihi'])) ?></p>
                    <p><strong>Sipariş Durumu:</strong> 
                        <span class="badge bg-<?= 
                            $order['Durum'] == 'Teslim Edildi' ? 'success' : 
                            ($order['Durum'] == 'Kargoda' ? 'info' : 
                            ($order['Durum'] == 'Hazırlanıyor' ? 'warning' : 'secondary')) ?>">
                            <?= $order['Durum'] ?>
                        </span>
                    </p>
                    <p><strong>Toplam Tutar:</strong> <?= number_format($order['ToplamTutar'], 2) ?> TL</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Ödeme Bilgileri</h5>
                </div>
                <div class="card-body">
                    <?php if ($payment): ?>
                        <p><strong>Ödeme Yöntemi:</strong> <?= $payment['ÖdemeYöntemi'] ?></p>
                        <p><strong>Ödeme Durumu:</strong> 
                            <span class="badge bg-<?= $payment['Durum'] == 'Başarılı' ? 'success' : ($payment['Durum'] == 'Başarısız' ? 'danger' : 'warning') ?>">
                                <?= $payment['Durum'] ?>
                            </span>
                        </p>
                        <p><strong>Ödeme Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($payment['ÖdemeTarihi'])) ?></p>
                    <?php else: ?>
                        <p>Ödeme bilgisi bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Müşteri Bilgileri</h5>
                </div>
                <div class="card-body">
                    <p><strong>Ad Soyad:</strong> <?= $order['Ad'] . ' ' . $order['Soyad'] ?></p>
                    <p><strong>Email:</strong> <?= $order['Email'] ?></p>
                    <p><strong>Telefon:</strong> <?= $order['Telefon'] ?></p>
                    <p><strong>Teslimat Adresi:</strong> <?= nl2br($order['TeslimatAdresi']) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5>Sipariş İçeriği</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Adet</th>
                            <th>Birim Fiyat</th>
                            <th>Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderDetails as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['ResimYolu'] ?: 'assets/images/no-image.jpg' ?>" alt="<?= $item['ÜrünAdı'] ?>" width="50" class="me-3">
                                        <div><?= $item['ÜrünAdı'] ?></div>
                                    </div>
                                </td>
                                <td><?= $item['Adet'] ?></td>
                                <td><?= number_format($item['BirimFiyat'], 2) ?> TL</td>
                                <td><?= number_format($item['ToplamFiyat'], 2) ?> TL</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-active">
                            <td colspan="3" class="text-end"><strong>Genel Toplam:</strong></td>
                            <td><strong><?= number_format($order['ToplamTutar'], 2) ?> TL</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="uye/panel.php" class="btn btn-primary">Siparişlerime Dön</a>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
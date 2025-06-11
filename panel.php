<?php
// uye/panel.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../uye/giris.php');
}

$userId = $_SESSION['user_id'];

// Kullanıcı bilgilerini getir
$stmt = $db->prepare("SELECT * FROM Kullanıcılar WHERE KullanıcıID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Siparişleri getir
$stmt = $db->prepare("SELECT * FROM Siparişler WHERE KullanıcıID = ? ORDER BY SiparişTarihi DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Hesabım</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="panel.php" class="list-group-item list-group-item-action active">Siparişlerim</a>
                <a href="bilgilerim.php" class="list-group-item list-group-item-action">Bilgilerim</a>
                <a href="cikis.php" class="list-group-item list-group-item-action">Çıkış Yap</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Siparişlerim</h5>
            </div>
            
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">Henüz siparişiniz bulunmamaktadır.</div>
                    <a href="../urunler.php" class="btn btn-primary">Alışverişe Başla</a>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sipariş No</th>
                                    <th>Tarih</th>
                                    <th>Tutar</th>
                                    <th>Durum</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['SiparişID'] ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['SiparişTarihi'])) ?></td>
                                        <td><?= number_format($order['ToplamTutar'], 2) ?> TL</td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['Durum'] == 'Teslim Edildi' ? 'success' : 
                                                ($order['Durum'] == 'Kargoda' ? 'info' : 
                                                ($order['Durum'] == 'Hazırlanıyor' ? 'warning' : 'secondary')) ?>">
                                                <?= $order['Durum'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="siparis-detay.php?id=<?= $order['SiparişID'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Detay
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
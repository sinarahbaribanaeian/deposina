<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header('Location: ../uye/giris.php');
    exit;
}

// Sipariş durumu güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE Siparişler SET Durum = ? WHERE SiparişID = ?");
    $stmt->execute([$status, $orderId]);
    
    $_SESSION['success_message'] = 'Sipariş durumu güncellendi!';
    header('Location: siparisler.php');
    exit;
}

// Siparişleri listele
$stmt = $db->query("
    SELECT s.*, k.Ad, k.Soyad 
    FROM Siparişler s
    JOIN Kullanıcılar k ON s.KullanıcıID = k.KullanıcıID
    ORDER BY s.SiparişTarihi DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Sipariş Yönetimi</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Müşteri</th>
                            <th>Tarih</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['SiparişID'] ?></td>
                                <td><?= $order['Ad'] . ' ' . $order['Soyad'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['SiparişTarihi'])) ?></td>
                                <td><?= number_format($order['ToplamTutar'], 2) ?> TL</td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['SiparişID'] ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="Beklemede" <?= $order['Durum'] == 'Beklemede' ? 'selected' : '' ?>>Beklemede</option>
                                            <option value="Hazırlanıyor" <?= $order['Durum'] == 'Hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                                            <option value="Kargoda" <?= $order['Durum'] == 'Kargoda' ? 'selected' : '' ?>>Kargoda</option>
                                            <option value="Teslim Edildi" <?= $order['Durum'] == 'Teslim Edildi' ? 'selected' : '' ?>>Teslim Edildi</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="siparis_detay.php?id=<?= $order['SiparişID'] ?>" class="btn btn-sm btn-info">Detay</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
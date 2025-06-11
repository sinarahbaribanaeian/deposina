<?php
require_once 'config.php';

// Kullanıcı giriş kontrolü
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Admin kontrolü
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] == 'admin';
}

// Ürünleri listeleme
function getProducts($categoryId = null, $search = null, $limit = null) {
    global $db;
    
    $sql = "SELECT u.*, k.KategoriAdı, 
            CASE WHEN u.İndirimOranı > 0 THEN u.Fiyat * (1 - u.İndirimOranı / 100) ELSE u.Fiyat END AS IndirimliFiyat
            FROM Ürünler u
            LEFT JOIN Kategoriler k ON u.KategoriID = k.KategoriID
            WHERE 1=1";
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND (u.KategoriID = ? OR k.ÜstKategoriID = ?)";
        $params[] = $categoryId;
        $params[] = $categoryId;
    }
    
    if ($search) {
        $sql .= " AND (u.ÜrünAdı LIKE ? OR u.Açıklama LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY u.EklenmeTarihi DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Kategorileri getir
function getCategories($parentId = null) {
    global $db;
    
    $sql = "SELECT * FROM Kategoriler";
    $params = [];
    
    if ($parentId === null) {
        $sql .= " WHERE ÜstKategoriID IS NULL";
    } elseif ($parentId !== false) {
        $sql .= " WHERE ÜstKategoriID = ?";
        $params[] = $parentId;
    }
    
    $sql .= " ORDER BY KategoriAdı";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Sepet işlemleri
function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function getCartItems() {
    if (empty($_SESSION['cart'])) {
        return [];
    }
    
    global $db;
    
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    
    $sql = "SELECT u.*, 
            CASE WHEN u.İndirimOranı > 0 THEN u.Fiyat * (1 - u.İndirimOranı / 100) ELSE u.Fiyat END AS IndirimliFiyat
            FROM Ürünler u 
            WHERE u.ÜrünID IN ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute($productIds);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $cartItems = [];
    foreach ($products as $product) {
        $cartItems[] = [
            'product' => $product,
            'quantity' => $_SESSION['cart'][$product['ÜrünID']],
            'total' => ($product['IndirimliFiyat'] ?? $product['Fiyat']) * $_SESSION['cart'][$product['ÜrünID']]
        ];
    }
    
    return $cartItems;
}

function getCartTotal() {
    $cartItems = getCartItems();
    $total = 0;
    
    foreach ($cartItems as $item) {
        $total += $item['total'];
    }
    
    return $total;
}

// Sipariş oluştur
function createOrder($userId, $address) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Sepet ürünlerini kontrol et
        $cartItems = getCartItems();
        if (empty($cartItems)) {
            throw new Exception("Sepetiniz boş.");
        }
        
        // Stok kontrolü yap
        foreach ($cartItems as $item) {
            if ($item['product']['StokMiktarı'] < $item['quantity']) {
                throw new Exception($item['product']['ÜrünAdı'] . " ürününden yeterli stok yok.");
            }
        }
        
        // Toplam tutarı hesapla
        $total = getCartTotal();
        
        // Siparişi oluştur
        $stmt = $db->prepare("INSERT INTO Siparişler (KullanıcıID, ToplamTutar, TeslimatAdresi) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $total, $address]);
        $orderId = $db->lastInsertId();
        
        // Sipariş detaylarını ekle
        foreach ($cartItems as $item) {
            $stmt = $db->prepare("INSERT INTO SiparişDetayları (SiparişID, ÜrünID, Adet, BirimFiyat, ToplamFiyat) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item['product']['ÜrünID'],
                $item['quantity'],
                $item['product']['IndirimliFiyat'] ?? $item['product']['Fiyat'],
                $item['total']
            ]);
        }
        
        // Ödeme kaydı oluştur (varsayılan olarak kapıda ödeme)
        $stmt = $db->prepare("INSERT INTO Ödemeler (SiparişID, ÖdemeYöntemi, Tutar) VALUES (?, 'Kapıda Ödeme', ?)");
        $stmt->execute([$orderId, $total]);
        
        // Sepeti temizle
        unset($_SESSION['cart']);
        
        $db->commit();
        
        return $orderId;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// Kullanıcı girişi
function loginUser($email, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM Kullanıcılar WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['Şifre'])) {
        $_SESSION['user_id'] = $user['KullanıcıID'];
        $_SESSION['user_name'] = $user['Ad'] . ' ' . $user['Soyad'];
        $_SESSION['user_role'] = $user['Rol'];
        
        // Son giriş tarihini güncelle
        $stmt = $db->prepare("UPDATE Kullanıcılar SET SonGirişTarihi = NOW() WHERE KullanıcıID = ?");
        $stmt->execute([$user['KullanıcıID']]);
        
        return true;
    }
    
    return false;
}

// Kullanıcı kaydı
function registerUser($name, $surname, $email, $password, $phone = null, $address = null) {
    global $db;
    
    // Email kontrolü
    $stmt = $db->prepare("SELECT COUNT(*) FROM Kullanıcılar WHERE Email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Bu email adresi zaten kayıtlı.");
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO Kullanıcılar (Ad, Soyad, Email, Şifre, Telefon, Adres) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $surname, $email, $hashedPassword, $phone, $address]);
    
    return $db->lastInsertId();
}
?>
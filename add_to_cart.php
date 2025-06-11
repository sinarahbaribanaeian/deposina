<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$productId = (int)$_POST['product_id'];

// Ürünün var olup olmadığını kontrol et
$stmt = $db->prepare("SELECT ÜrünID FROM Ürünler WHERE ÜrünID = ?");
$stmt->execute([$productId]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı!']);
    exit;
}

// Sepete ekle
if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId]++;
} else {
    $_SESSION['cart'][$productId] = 1;
}

echo json_encode([
    'success' => true,
    'cart_count' => count($_SESSION['cart'])
]);
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Oturum kontrolü ve admin yetkisi kontrolü
session_start();
if (!isAdmin()) {
    header('Location: ../uye/giris.php');
    exit;
}

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name']);
    $description = trim($_POST['description']);
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    // Giriş doğrulama
    if (empty($categoryName)) {
        $_SESSION['error_message'] = 'Kategori adı boş bırakılamaz!';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO Kategoriler (KategoriAdı, Açıklama, ÜstKategoriID) VALUES (?, ?, ?)");
            $stmt->execute([$categoryName, $description, $parentId]);
            
            $_SESSION['success_message'] = 'Kategori başarıyla eklendi!';
            header('Location: kategoriler.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Kategori eklenirken hata oluştu: ' . $e->getMessage();
        }
    }
}

// Kategori silme işlemi
if (isset($_GET['delete'])) {
    $categoryId = (int)$_GET['delete'];
    
    try {
        // Alt kategorileri kontrol et
        $stmt = $db->prepare("SELECT COUNT(*) FROM Kategoriler WHERE ÜstKategoriID = ?");
        $stmt->execute([$categoryId]);
        $subcategoryCount = $stmt->fetchColumn();
        
        if ($subcategoryCount > 0) {
            $_SESSION['error_message'] = 'Bu kategorinin alt kategorileri var, önce onları silmelisiniz.';
        } else {
            // Kategoriye bağlı ürünleri kontrol et
            $stmt = $db->prepare("SELECT COUNT(*) FROM Ürünler WHERE KategoriID = ?");
            $stmt->execute([$categoryId]);
            $productCount = $stmt->fetchColumn();
            
            if ($productCount > 0) {
                $_SESSION['error_message'] = 'Bu kategoride ürünler var, önce
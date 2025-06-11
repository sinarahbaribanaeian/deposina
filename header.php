<?php
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
session_start();
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sina Kırtasiye</title>
    <link href="<?= $base_url ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/fontawesome.min.css" rel="stylesheet">
    <link href="<?= $base_url ?>assets/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= $base_url ?>">S</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>">Anasayfa</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>urunler.php">Ürünler</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>kategoriler.php">Kategoriler</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_url ?>sepet.php">
                        <i class="fas fa-shopping-cart"></i> Sepet
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <span class="badge bg-danger cart-count"><?= count($_SESSION['cart']) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= $base_url ?>uye/panel.php">Hesabım</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?= $base_url ?>admin/">Yönetim Paneli</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>uye/cikis.php">Çıkış Yap</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>uye/giris.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_url ?>uye/kayit.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Oturumu sonlandır
session_unset();
session_destroy();

// Ana sayfaya yönlendir
header('Location: ../index.php');
exit;
?>
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT * FROM Kullanıcılar WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['Şifre'])) {
        $_SESSION['user_id'] = $user['KullanıcıID'];
        $_SESSION['user_name'] = $user['Ad'] . ' ' . $user['Soyad'];
        $_SESSION['user_role'] = $user['Rol'];
        
        header('Location: ../uye/panel.php');
        exit;
    } else {
        $error = "Geçersiz email veya şifre!";
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Giriş Yap</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Giriş Yap</button>
        </form>
        
        <div class="mt-3">
            <p>Hesabınız yok mu? <a href="kayit.php">Kayıt Olun</a></p>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    try {
        $stmt = $db->prepare("INSERT INTO Kullanıcılar (Ad, Soyad, Email, Şifre, Telefon, Adres) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $surname, $email, $password, $phone, $address]);
        
        $_SESSION['user_id'] = $db->lastInsertId();
        $_SESSION['user_name'] = $name . ' ' . $surname;
        $_SESSION['user_role'] = 'user';
        
        header('Location: panel.php');
        exit;
    } catch (PDOException $e) {
        $error = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Kayıt Ol</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Ad</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="surname" class="form-label">Soyad</label>
                    <input type="text" class="form-control" id="surname" name="surname" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Telefon</label>
                <input type="tel" class="form-control" id="phone" name="phone">
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Adres</label>
                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Kayıt Ol</button>
        </form>
        
        <div class="mt-3">
            <p>Zaten hesabınız var mı? <a href="giris.php">Giriş Yapın</a></p>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
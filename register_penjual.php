<?php
session_start();
include 'koneksi.php';

// Jika sudah login, lempar ke halaman login
if (isset($_SESSION['role'])) {
    header("Location: login.php"); exit;
}

// =========================================================================
// SIHIR AUTO-UPDATE PINTAR: Memastikan tabel siap menerima penjual
// =========================================================================
$kolom_tambahan = [
    'no_hp' => "VARCHAR(20) NULL AFTER role",
    'alamat_lengkap' => "TEXT NULL AFTER no_hp",
    'saldo' => "INT DEFAULT 0 AFTER alamat_lengkap",
    'sella_coins' => "INT DEFAULT 0 AFTER saldo"
];

foreach ($kolom_tambahan as $kolom => $definisi) {
    $cek = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$kolom'");
    if (mysqli_num_rows($cek) == 0) {
        mysqli_query($conn, "ALTER TABLE users ADD $kolom $definisi");
    }
}
// =========================================================================

$error = '';
$success = '';

if (isset($_POST['register_toko'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);
    $password_mentah = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Cek apakah nama toko (username) sudah ada yang pakai
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = 'Nama Toko (Username) sudah digunakan, silakan cari nama lain yang lebih keren.';
    } else {
        $password_aman = password_hash($password_mentah, PASSWORD_DEFAULT);
        
        // PERHATIKAN BARIS INI: Role diset menjadi 'penjual'
        $query = "INSERT INTO users (username, password, role, no_hp, alamat_lengkap, saldo, sella_coins) 
                  VALUES ('$username', '$password_aman', 'penjual', '$no_hp', '$alamat', 0, 0)";
                  
        if (mysqli_query($conn, $query)) {
            $success = 'Toko berhasil dibuat! Silakan login untuk mulai mengelola produk Anda.';
        } else {
            $error = 'Gagal membuat toko: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Buka Toko - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); font-family: 'Courier New', Courier, monospace; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: #fff; padding: 40px 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(250, 89, 29, 0.1); width: 100%; max-width: 450px; border-top: 5px solid #fa591d; }
        .form-control { border-radius: 10px; padding: 12px 15px; background-color: #f8f9fa; border: 1px solid #eee; }
        .form-control:focus { box-shadow: none; border-color: #fa591d; background-color: #fff; }
        .btn-login { background: linear-gradient(135deg, #fa591d, #ff7a45); color: white; border-radius: 10px; font-weight: bold; padding: 12px; border: none; transition: 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(250,89,29,0.4); color: white; }
        .link-custom { color: #fa591d; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark m-0"><i class="bi bi-shop text-warning me-2"></i>Buka Toko SELLA</h3>
        <p class="text-muted small">Mulai bisnis Anda dan raih jutaan pelanggan!</p>
    </div>

    <?php if ($error != ''): ?>
        <div class="alert alert-danger p-2 small text-center rounded-3 border-0 bg-danger bg-opacity-10 text-danger fw-bold"><i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success != ''): ?>
        <div class="alert alert-success p-2 small text-center rounded-3 border-0 bg-success bg-opacity-10 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> <?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Action mengarah ke file ini sendiri -->
    <form action="register_penjual.php" method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Nama Toko (Username)</label>
            <input type="text" name="username" class="form-control" placeholder="Contoh: SepatuBerkah" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Nomor WhatsApp Toko</label>
            <input type="number" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Alamat Toko</label>
            <textarea name="alamat_lengkap" class="form-control" rows="2" placeholder="Masukkan alamat asal pengiriman" required></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold small text-muted">Password Akun</label>
            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
        </div>
        
        <button type="submit" name="register_toko" class="btn-login w-100 mb-3"><i class="bi bi-shop-window me-2"></i>Daftar Sebagai Penjual</button>
    </form>
    
    <div class="text-center mt-3 small">
        Sudah punya toko? <a href="login.php" class="link-custom">Masuk di sini</a>
    </div>
</div>

</body>
</html>
<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['role'])) {
    header("Location: login.php"); exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);
    $password_mentah = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Pengecekan apakah username sudah dipakai
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = 'Username sudah digunakan, silakan pilih yang lain.';
    } else {
        // --- PROSES PASSWORD HASHING ---
        $password_aman = password_hash($password_mentah, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, password, role, no_hp, alamat_lengkap, saldo, sella_coins) 
                  VALUES ('$username', '$password_aman', 'pembeli', '$no_hp', '$alamat', 0, 0)";
                  
        if (mysqli_query($conn, $query)) {
            $success = 'Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.';
        } else {
            $error = 'Gagal mendaftar. Terjadi kesalahan pada server.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daftar Akun - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background: linear-gradient(135deg, #f4f6f9 0%, #e8f5e9 100%); font-family: 'Courier New', Courier, monospace; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: #fff; padding: 40px 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(17, 138, 68, 0.1); width: 100%; max-width: 450px; border-top: 5px solid #118a44; }
        .form-control { border-radius: 10px; padding: 12px 15px; background-color: #f8f9fa; border: 1px solid #eee; }
        .form-control:focus { box-shadow: none; border-color: #118a44; background-color: #fff; }
        .btn-login { background: linear-gradient(135deg, #118a44, #18b35a); color: white; border-radius: 10px; font-weight: bold; padding: 12px; border: none; transition: 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(17,138,68,0.4); color: white; }
        .link-custom { color: #118a44; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark m-0">Buat Akun SELLA</h3>
        <p class="text-muted small">Mulai belanja dengan aman dan mudah</p>
    </div>

    <?php if ($error != ''): ?>
        <div class="alert alert-danger p-2 small text-center rounded-3 border-0 bg-danger bg-opacity-10 text-danger fw-bold"><i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success != ''): ?>
        <div class="alert alert-success p-2 small text-center rounded-3 border-0 bg-success bg-opacity-10 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> <?php echo $success; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Pilih username" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Nomor HP</label>
            <input type="number" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Alamat Lengkap</label>
            <textarea name="alamat_lengkap" class="form-control" rows="2" placeholder="Masukkan alamat pengiriman" required></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold small text-muted">Password Baru</label>
            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
        </div>
        
        <button type="submit" name="register" class="btn-login w-100 mb-3"><i class="bi bi-person-plus me-2"></i>Daftar Sekarang</button>
    </form>
    
    <div class="text-center mt-3 small">
        Sudah punya akun? <a href="login.php" class="link-custom">Masuk di sini</a>
    </div>
</div>

</body>
</html>
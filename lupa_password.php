<?php
session_start();
include 'koneksi.php';

// Jika sudah login, lempar ke halaman utama
if (isset($_SESSION['role'])) {
    header("Location: login.php"); exit;
}

$error = '';
$success = '';
$step = 1; // Step 1: Cek Username & No HP | Step 2: Input Password Baru

// PROSES STEP 1: VERIFIKASI DATA
if (isset($_POST['cek_data'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' AND no_hp = '$no_hp'");
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        $_SESSION['reset_user_id'] = $data['id']; // Simpan ID sementara untuk direset
        $step = 2; // Lanjut ke form password baru
    } else {
        $error = 'Data tidak cocok! Periksa kembali Username dan Nomor HP Anda.';
    }
}

// PROSES STEP 2: SIMPAN PASSWORD BARU
if (isset($_POST['reset_password'])) {
    $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    $id_reset = $_SESSION['reset_user_id'];
    
    // Hash password baru untuk keamanan
    $password_aman = password_hash($password_baru, PASSWORD_DEFAULT);
    
    $update = mysqli_query($conn, "UPDATE users SET password = '$password_aman' WHERE id = '$id_reset'");
    
    if ($update) {
        unset($_SESSION['reset_user_id']); // Hapus session reset
        $success = 'Password berhasil diubah! Silakan login dengan password baru Anda.';
        $step = 3; // Step selesai
    } else {
        $error = 'Gagal mereset password. Terjadi kesalahan sistem.';
        $step = 2;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lupa Password - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background: linear-gradient(135deg, #f4f6f9 0%, #e8f5e9 100%); font-family: 'Courier New', Courier, monospace; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: #fff; padding: 40px 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(17, 138, 68, 0.1); width: 100%; max-width: 400px; border-top: 5px solid #dc3545; }
        .form-control { border-radius: 10px; padding: 12px 15px; background-color: #f8f9fa; border: 1px solid #eee; }
        .form-control:focus { box-shadow: none; border-color: #dc3545; background-color: #fff; }
        .btn-custom { background: linear-gradient(135deg, #dc3545, #fd7e14); color: white; border-radius: 10px; font-weight: bold; padding: 12px; border: none; transition: 0.3s; }
        .btn-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(220,53,69,0.4); color: white; }
        .link-custom { color: #118a44; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="text-danger mb-3"><i class="bi bi-shield-lock-fill" style="font-size: 50px;"></i></div>
        <h4 class="fw-bold text-dark m-0">Lupa Password</h4>
        <p class="text-muted small">Sistem Pemulihan Akun SELLA</p>
    </div>

    <?php if ($error != ''): ?>
        <div class="alert alert-danger p-2 small text-center rounded-3 border-0 bg-danger bg-opacity-10 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- TAHAP 1: VERIFIKASI DATA -->
    <?php if($step == 1): ?>
    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Username Anda</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold small text-muted">Nomor HP Terdaftar</label>
            <input type="number" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
        </div>
        <button type="submit" name="cek_data" class="btn-custom w-100 mb-3"><i class="bi bi-search me-2"></i>Verifikasi Data</button>
    </form>
    <?php endif; ?>

    <!-- TAHAP 2: INPUT PASSWORD BARU -->
    <?php if($step == 2): ?>
    <div class="alert alert-success p-2 small text-center rounded-3 border-0 bg-success bg-opacity-10 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Data ditemukan! Silakan buat password baru.</div>
    <form action="" method="POST">
        <div class="mb-4">
            <label class="form-label fw-bold small text-muted">Password Baru</label>
            <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required autofocus>
        </div>
        <button type="submit" name="reset_password" class="btn-custom w-100 mb-3" style="background: linear-gradient(135deg, #118a44, #18b35a);"><i class="bi bi-save me-2"></i>Simpan Password</button>
    </form>
    <?php endif; ?>

    <!-- TAHAP 3: SUKSES -->
    <?php if($step == 3): ?>
    <div class="alert alert-success p-3 text-center rounded-3 border-0 bg-success bg-opacity-10 text-success fw-bold">
        <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
        <?php echo $success; ?>
    </div>
    <a href="login.php" class="btn-custom w-100 mb-3 d-block text-center text-decoration-none" style="background: linear-gradient(135deg, #118a44, #18b35a);">Kembali ke Login</a>
    <?php endif; ?>
    
    <?php if($step != 3): ?>
    <div class="text-center mt-3 small">
        <a href="login.php" class="link-custom"><i class="bi bi-arrow-left me-1"></i> Kembali ke Login</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
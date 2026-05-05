<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') header("Location: dashboard_admin.php");
    else if ($_SESSION['role'] == 'penjual') header("Location: dashboard_penjual.php");
    else header("Location: marketplace_pembeli.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = mysqli_real_escape_string($conn, $_POST['password']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        
        if (password_verify($password_input, $data['password'])) { 
            $_SESSION['id'] = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['role'];

            if ($data['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } else if ($data['role'] == 'penjual') {
                header("Location: dashboard_penjual.php");
            } else {
                header("Location: marketplace_pembeli.php");
            }
            exit;
        } else {
            $error = 'Password yang Anda masukkan salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background: linear-gradient(135deg, #f4f6f9 0%, #e8f5e9 100%); font-family: 'Courier New', Courier, monospace; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: #fff; padding: 40px 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(17, 138, 68, 0.1); width: 100%; max-width: 400px; border-top: 5px solid #118a44; }
        .brand-logo { width: 60px; height: 60px; background: #118a44; color: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 15px auto; transform: rotate(-10deg); box-shadow: 0 5px 15px rgba(17,138,68,0.3); }
        .form-control { border-radius: 10px; padding: 12px 15px; background-color: #f8f9fa; border: 1px solid #eee; }
        .form-control:focus { box-shadow: none; border-color: #118a44; background-color: #fff; }
        .btn-login { background: linear-gradient(135deg, #118a44, #18b35a); color: white; border-radius: 10px; font-weight: bold; padding: 12px; border: none; transition: 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(17,138,68,0.4); color: white; }
        .link-custom { color: #118a44; text-decoration: none; font-weight: bold; }
        .link-custom:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-logo"><i class="bi bi-bag-heart-fill"></i></div>
        <h3 class="fw-bold text-dark m-0">SELLA</h3>
        <p class="text-muted small">Marketplace Terpercaya Anda</p>
    </div>

    <?php if ($error != ''): ?>
        <div class="alert alert-danger p-2 small text-center rounded-3 border-0 bg-danger bg-opacity-10 text-danger fw-bold"><i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Username</label>
            <div class="input-group">
                <span class="input-group-text border-0 bg-light text-muted"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username" required autofocus>
            </div>
        </div>
        
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label fw-bold small text-muted m-0">Password</label>
                <!-- TAMBAHAN LINK LUPA PASSWORD -->
                <a href="lupa_password.php" class="small text-danger text-decoration-none fw-bold">Lupa Password?</a>
            </div>
            <div class="input-group mt-2">
                <span class="input-group-text border-0 bg-light text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="passInput" class="form-control border-start-0 border-end-0 px-0" placeholder="Masukkan password" required>
                <span class="input-group-text border-0 bg-light text-muted" style="cursor: pointer;" onclick="togglePass()">
                    <i class="bi bi-eye-slash" id="eyeIcon"></i>
                </span>
            </div>
        </div>
        
        <button type="submit" name="login" class="btn-login w-100 mt-4 mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk Sekarang</button>
    </form>
    
    <div class="text-center mt-3 small">
        Belum punya akun? <br>
        <a href="register.php" class="link-custom">Daftar sebagai Pembeli</a> atau <a href="register_penjual.php" class="link-custom">Buka Toko</a>
    </div>
</div>

<script>
    function togglePass() {
        var x = document.getElementById("passInput");
        var icon = document.getElementById("eyeIcon");
        if (x.type === "password") {
            x.type = "text";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        } else {
            x.type = "password";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        }
    }
</script>

</body>
</html>
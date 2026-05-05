<?php
session_start();
include 'koneksi.php';

// Proteksi tingkat tinggi: Hanya penjual yang boleh masuk ke brankas!
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];
$pesan = '';

// PROSES PENARIKAN SALDO (SIMULASI)
if (isset($_POST['tarik_saldo'])) {
    $nominal_tarik = (int)$_POST['nominal'];
    
    // Ambil saldo terbaru sebelum ditarik
    $q_cek = mysqli_query($conn, "SELECT saldo FROM users WHERE id = '$penjual_id'");
    $data_cek = mysqli_fetch_assoc($q_cek);
    $saldo_tersedia = $data_cek['saldo'];
    
    if ($nominal_tarik < 10000) {
        $pesan = '<div class="alert alert-warning fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Minimal penarikan adalah Rp 10.000.</div>';
    } elseif ($nominal_tarik > $saldo_tersedia) {
        $pesan = '<div class="alert alert-danger fw-bold"><i class="bi bi-x-circle me-2"></i>Saldo Anda tidak mencukupi untuk penarikan ini!</div>';
    } else {
        // Potong saldo penjual
        $update_saldo = mysqli_query($conn, "UPDATE users SET saldo = saldo - $nominal_tarik WHERE id = '$penjual_id'");
        
        if ($update_saldo) {
            $pesan = '<div class="alert alert-success fw-bold"><i class="bi bi-check-circle me-2"></i>Berhasil! Penarikan sebesar Rp ' . number_format($nominal_tarik, 0, ',', '.') . ' sedang diproses ke rekening Anda. (Simulasi)</div>';
        } else {
            $pesan = '<div class="alert alert-danger fw-bold">Gagal memproses penarikan sistem.</div>';
        }
    }
}

// AMBIL SALDO TERBARU UNTUK DITAMPILKAN DI LAYAR
$q_user = mysqli_query($conn, "SELECT saldo FROM users WHERE id = '$penjual_id'");
$user = mysqli_fetch_assoc($q_user);
$saldo_sekarang = $user['saldo'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Keuangan Toko - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Courier New', Courier, monospace; color: #333; padding-bottom: 50px; }
        
        /* HEADER */
        .seller-nav { background: #fff; padding: 15px 0; border-bottom: 3px solid #fa591d; box-shadow: 0 2px 15px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1030; }
        .seller-nav a { color: #333; font-size: 20px; text-decoration: none; font-weight: bold; }
        
        /* KARTU SALDO UTAMA */
        .wallet-card { background: linear-gradient(135deg, #fa591d, #ff7a45); color: white; border-radius: 20px; padding: 35px 25px; box-shadow: 0 10px 25px rgba(250, 89, 29, 0.3); position: relative; overflow: hidden; }
        .wallet-card::after { content: ''; position: absolute; top: -30px; right: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; }
        .wallet-card::before { content: ''; position: absolute; bottom: -20px; left: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; }
        
        /* KARTU FORM PENARIKAN */
        .form-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); border: 1px solid #eee; }
        
        .form-control-custom { border-radius: 10px; padding: 15px; border: 2px solid #eee; font-size: 18px; font-weight: bold; background-color: #fcfcfc; }
        .form-control-custom:focus { border-color: #fa591d; box-shadow: none; background-color: #fff; }
        
        .btn-orange { background: #fa591d; color: white; font-weight: bold; border-radius: 30px; padding: 15px; border: none; transition: 0.3s; box-shadow: 0 5px 15px rgba(250,89,29,0.3); width: 100%; font-size: 18px; }
        .btn-orange:hover { background: #e04b14; color: white; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(250,89,29,0.4); }
        .btn-orange:disabled { background: #ccc; box-shadow: none; transform: none; cursor: not-allowed; }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="seller-nav">
    <div class="container d-flex align-items-center">
        <a href="dashboard_penjual.php" class="me-3"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Keuangan Toko</h5>
    </div>
</div>

<div class="container mt-4" style="max-width: 700px;">
    
    <?php echo $pesan; ?>

    <!-- KARTU INFO SALDO -->
    <div class="wallet-card mb-4 text-center text-md-start">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div style="z-index: 1;">
                <p class="mb-1 opacity-75 fw-bold text-uppercase" style="letter-spacing: 1px;">Total Saldo Penghasilan</p>
                <h1 class="fw-bold m-0" style="font-size: 40px;">Rp <?php echo number_format($saldo_sekarang, 0, ',', '.'); ?></h1>
            </div>
            <i class="bi bi-wallet2 d-none d-md-block opacity-50" style="font-size: 80px; z-index: 1;"></i>
        </div>
    </div>

    <!-- FORM PENARIKAN -->
    <div class="form-card">
        <h5 class="fw-bold mb-4"><i class="bi bi-cash-coin text-warning me-2"></i>Tarik Dana ke Rekening</h5>
        
        <form action="" method="POST">
            <div class="mb-4">
                <label class="form-label text-muted fw-bold small">Nominal Penarikan (Rp)</label>
                <input type="number" name="nominal" class="form-control form-control-custom" placeholder="0" min="10000" required>
                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i> Minimal penarikan Rp 10.000. Uang akan dipotong dari saldo Anda.</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-muted fw-bold small">Pilih Bank Tujuan</label>
                <select class="form-select form-control-custom" style="font-size: 16px;">
                    <option value="bca">BCA - Bank Central Asia</option>
                    <option value="mandiri">Bank Mandiri</option>
                    <option value="bni">BNI - Bank Negara Indonesia</option>
                    <option value="bri">BRI - Bank Rakyat Indonesia</option>
                    <option value="ewallet">DANA / OVO / GoPay</option>
                </select>
            </div>
            
            <button type="submit" name="tarik_saldo" class="btn-orange" <?php echo ($saldo_sekarang < 10000) ? 'disabled' : ''; ?>>
                <i class="bi bi-bank me-2"></i> Proses Penarikan
            </button>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
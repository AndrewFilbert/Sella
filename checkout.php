<?php
session_start();
include 'koneksi.php';

// Pastikan yang mengakses ini adalah pembeli
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$pembeli_id = $_SESSION['id'];
$error = '';
$success = '';

// SIHIR AUTO-UPDATE DATABASE: Otomatis bikin tabel orders jika belum ada
$cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");
if(mysqli_num_rows($cek_tabel) == 0){
    mysqli_query($conn, "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pembeli_id INT NOT NULL,
        total_bayar INT NOT NULL,
        biaya_admin INT DEFAULT 0,
        status_pesanan VARCHAR(50) DEFAULT 'dikemas',
        tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

// 1. AMBIL SALDO PEMBELI SAAT INI
$q_user = mysqli_query($conn, "SELECT saldo, alamat_lengkap FROM users WHERE id = '$pembeli_id'");
$user = mysqli_fetch_assoc($q_user);
$saldo_sekarang = $user['saldo'];
$alamat_pengiriman = $user['alamat_lengkap'];

// 2. SIMULASI TOTAL BELANJA (Ambil dari URL jika ada, misal: checkout.php?total=150000)
// Jika tidak ada di URL, kita anggap default belanjanya Rp 100.000 untuk keperluan testing
$total_harga_barang = isset($_GET['total']) ? (int)$_GET['total'] : 100000;

// 3. RUMUS BIAYA ADMIN (Contoh: 2%)
$persen_admin = 0.02; 
$biaya_admin = $total_harga_barang * $persen_admin;
$total_tagihan = $total_harga_barang + $biaya_admin;

// 4. PROSES PEMBAYARAN KETIKA TOMBOL DITEKAN
if (isset($_POST['bayar_sekarang'])) {
    
    // Cek apakah saldo cukup?
    if ($saldo_sekarang >= $total_tagihan) {
        
        // A. Potong saldo pembeli
        $potong_saldo = mysqli_query($conn, "UPDATE users SET saldo = saldo - $total_tagihan WHERE id = '$pembeli_id'");
        
        if ($potong_saldo) {
            // B. Masukkan data ke tabel pesanan (orders)
            $query_order = "INSERT INTO orders (pembeli_id, total_bayar, biaya_admin, status_pesanan, tanggal_transaksi) 
                            VALUES ('$pembeli_id', '$total_harga_barang', '$biaya_admin', 'dikemas', NOW())";
            mysqli_query($conn, $query_order);
            
            $success = "Pembayaran Berhasil! Pesanan Anda sedang diproses oleh penjual.";
            
            // Perbarui tampilan saldo di layar setelah dipotong
            $saldo_sekarang -= $total_tagihan; 
        } else {
            $error = "Terjadi kesalahan pada sistem pemotongan saldo.";
        }
        
    } else {
        $error = "Saldo SELLA Pay Anda tidak mencukupi. Silakan Top-Up di menu Dompet.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Checkout - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; padding-bottom: 50px; }
        
        .top-nav { background: #fff; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .top-nav a { color: #333; font-size: 24px; text-decoration: none; margin-right: 15px; }
        
        .card-custom { background: #fff; border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; overflow: hidden; }
        .card-header-custom { background: linear-gradient(135deg, #118a44, #18b35a); color: white; padding: 15px 20px; font-weight: bold; }
        
        .rincian-baris { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; }
        .rincian-total { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #eee; font-size: 18px; font-weight: bold; color: #118a44; }
        
        .wallet-box { background: rgba(17, 138, 68, 0.05); border: 1px solid rgba(17, 138, 68, 0.2); border-radius: 10px; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        
        .btn-bayar { background: linear-gradient(135deg, #118a44, #18b35a); color: white; border-radius: 30px; font-weight: bold; padding: 15px; border: none; transition: 0.3s; width: 100%; font-size: 18px; box-shadow: 0 5px 15px rgba(17,138,68,0.3); }
        .btn-bayar:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(17,138,68,0.4); color: white; }
        .btn-bayar:disabled { background: #ccc; box-shadow: none; transform: none; }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="container d-flex align-items-center">
        <!-- Anggap kembali ke keranjang -->
        <a href="marketplace_pembeli.php"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Checkout Pesanan</h5>
    </div>
</div>

<div class="container" style="max-width: 600px;">

    <?php if ($error != ''): ?>
        <div class="alert alert-danger rounded-3 fw-bold shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success != ''): ?>
        <div class="alert alert-success rounded-3 fw-bold shadow-sm text-center">
            <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
            <?php echo $success; ?><br>
            <a href="pesanan_saya.php" class="btn btn-sm btn-success rounded-pill mt-3 px-4">Lihat Pesanan Saya</a>
        </div>
    <?php else: ?>

    <form action="" method="POST">
        <!-- KARTU ALAMAT -->
        <div class="card-custom">
            <div class="card-body p-4">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Alamat Pengiriman</h6>
                <p class="m-0 fw-bold"><?php echo $_SESSION['username']; ?></p>
                <p class="m-0 text-muted small mt-1" style="line-height: 1.5;"><?php echo $alamat_pengiriman != '' ? $alamat_pengiriman : 'Alamat belum diatur di profil Anda.'; ?></p>
            </div>
        </div>

        <!-- KARTU RINCIAN PEMBAYARAN -->
        <div class="card-custom">
            <div class="card-header-custom">
                <i class="bi bi-receipt me-2"></i>Rincian Tagihan
            </div>
            <div class="card-body p-4">
                <div class="rincian-baris">
                    <span class="text-muted">Total Harga Barang</span>
                    <span class="fw-bold">Rp <?php echo number_format($total_harga_barang, 0, ',', '.'); ?></span>
                </div>
                <div class="rincian-baris">
                    <span class="text-muted">Biaya Admin (2%) <i class="bi bi-info-circle ms-1 text-primary" title="Biaya pemeliharaan sistem"></i></span>
                    <span class="fw-bold text-danger">+ Rp <?php echo number_format($biaya_admin, 0, ',', '.'); ?></span>
                </div>
                
                <div class="rincian-total">
                    <span>Total Belanja</span>
                    <span>Rp <?php echo number_format($total_tagihan, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <!-- KARTU METODE PEMBAYARAN -->
        <div class="card-custom">
            <div class="card-body p-4">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-credit-card-fill text-primary me-2"></i>Metode Pembayaran</h6>
                
                <div class="wallet-box">
                    <div>
                        <div class="fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>SELLA Pay</div>
                        <div class="small text-muted mt-1">Saldo Anda: Rp <?php echo number_format($saldo_sekarang, 0, ',', '.'); ?></div>
                    </div>
                    <?php if($saldo_sekarang < $total_tagihan): ?>
                        <span class="badge bg-danger rounded-pill px-3 py-2">Saldo Kurang</span>
                    <?php else: ?>
                        <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-lg"></i> Mencukupi</span>
                    <?php endif; ?>
                </div>
                
                <?php if($saldo_sekarang < $total_tagihan): ?>
                    <div class="text-center mt-3">
                        <a href="dompet_pembeli.php" class="small text-decoration-none fw-bold text-success"><i class="bi bi-plus-circle me-1"></i>Top-Up Saldo Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TOMBOL BAYAR -->
        <button type="submit" name="bayar_sekarang" class="btn-bayar mt-2 mb-4" <?php echo ($saldo_sekarang < $total_tagihan) ? 'disabled' : ''; ?>>
            <i class="bi bi-shield-lock-fill me-2"></i>Bayar Rp <?php echo number_format($total_tagihan, 0, ',', '.'); ?>
        </button>

    </form>
    
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
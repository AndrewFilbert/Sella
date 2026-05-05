<?php
session_start();
include 'koneksi.php';

// Proteksi: Hanya penjual yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];
$nama_toko = $_SESSION['username'];

// =========================================================================
// SIHIR AUTO-UPDATE: Memastikan tabel produk dan relasi pesanan siap
// =========================================================================
// 1. Buat tabel produk jika belum ada
$cek_tabel_produk = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
if(mysqli_num_rows($cek_tabel_produk) == 0){
    mysqli_query($conn, "CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        penjual_id INT NOT NULL,
        nama_produk VARCHAR(255) NOT NULL,
        deskripsi TEXT,
        harga INT NOT NULL,
        stok INT DEFAULT 0,
        kategori VARCHAR(100),
        gambar VARCHAR(255),
        tanggal_upload DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

// 2. Pastikan tabel orders punya kolom penjual_id untuk melacak pesanan toko ini
$cek_kolom_penjual = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'penjual_id'");
if(mysqli_num_rows($cek_kolom_penjual) == 0){
    mysqli_query($conn, "ALTER TABLE orders ADD penjual_id INT NULL AFTER pembeli_id");
}
// =========================================================================

// --- MENGAMBIL DATA STATISTIK TOKO ---
// 1. Total Produk
$q_produk = mysqli_query($conn, "SELECT COUNT(id) as total FROM products WHERE penjual_id = '$penjual_id'");
$total_produk = mysqli_fetch_assoc($q_produk)['total'] ?? 0;

// 2. Pesanan Perlu Dikirim (Dikemas)
$q_pesanan = mysqli_query($conn, "SELECT COUNT(id) as total FROM orders WHERE penjual_id = '$penjual_id' AND status_pesanan = 'dikemas'");
$pesanan_baru = mysqli_fetch_assoc($q_pesanan)['total'] ?? 0;

// 3. Total Pendapatan Kotor (Pesanan Selesai)
$q_pendapatan = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM orders WHERE penjual_id = '$penjual_id' AND status_pesanan = 'selesai'");
$total_pendapatan = mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0;

// 4. Ambil 5 Pesanan Terbaru
$q_pesanan_terbaru = mysqli_query($conn, "SELECT o.*, u.username as nama_pembeli FROM orders o LEFT JOIN users u ON o.pembeli_id = u.id WHERE o.penjual_id = '$penjual_id' ORDER BY o.id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Seller Center - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Courier New', Courier, monospace; color: #333; }
        
        /* HEADER KHUSUS PENJUAL */
        .seller-nav { background: #fff; padding: 15px 0; border-bottom: 3px solid #fa591d; box-shadow: 0 2px 15px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1030; }
        .seller-brand { color: #fa591d; font-weight: 900; font-size: 24px; text-decoration: none; display: flex; align-items: center; }
        
        /* WELCOME BANNER */
        .welcome-banner { background: linear-gradient(135deg, #fa591d, #ff7a45); border-radius: 15px; color: white; padding: 30px; margin-top: 30px; box-shadow: 0 10px 20px rgba(250, 89, 29, 0.2); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        
        /* KARTU STATISTIK */
        .stat-card { background: #fff; border-radius: 15px; padding: 25px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: 0.3s; height: 100%; border-left: 5px solid #fa591d; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(250, 89, 29, 0.15); }
        .stat-icon { width: 50px; height: 50px; background: rgba(250, 89, 29, 0.1); color: #fa591d; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; }
        
        /* KARTU AKSI CEPAT */
        .action-card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); text-align: center; text-decoration: none; color: #333; display: block; transition: 0.3s; border: 1px solid #eee; }
        .action-card:hover { border-color: #fa591d; color: #fa591d; background: rgba(250, 89, 29, 0.02); }
        .action-card i { font-size: 40px; margin-bottom: 10px; color: #fa591d; display: block; }
        
        /* TABEL PESANAN */
        .table-container { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .table thead th { background-color: #f8f9fa; color: #555; border-bottom: 2px solid #eee; font-weight: bold; }
        .table tbody td { vertical-align: middle; border-bottom: 1px solid #f1f1f1; }
        
        .badge-status { padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .badge-dikemas { background: rgba(250, 89, 29, 0.1); color: #fa591d; border: 1px solid #fa591d; }
        .badge-dikirim { background: rgba(13, 110, 253, 0.1); color: #0d6efd; border: 1px solid #0d6efd; }
        .badge-selesai { background: rgba(25, 135, 84, 0.1); color: #198754; border: 1px solid #198754; }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="seller-nav">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="dashboard_penjual.php" class="seller-brand">
            <i class="bi bi-shop-window me-2"></i> SELLA Seller
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="d-none d-md-inline fw-bold text-muted">Toko: <span class="text-dark"><?php echo $nama_toko; ?></span></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold rounded-pill px-3"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a>
        </div>
    </div>
</div>

<div class="container pb-5">
    
    <!-- BANNER SELAMAT DATANG -->
    <div class="welcome-banner mb-4">
        <div>
            <h3 class="fw-bold mb-2">Halo, Juragan <?php echo $nama_toko; ?>! 👋</h3>
            <p class="m-0 opacity-75">Pantau terus performa toko Anda dan tingkatkan penjualan hari ini.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="tambah_produk.php" class="btn btn-light fw-bold text-dark rounded-pill shadow-sm px-4 py-2">
                <i class="bi bi-plus-lg me-1 text-danger"></i> Tambah Produk Baru
            </a>
        </div>
    </div>

    <!-- BARIS STATISTIK -->
    <div class="row g-4 mb-5">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                <div class="text-muted small fw-bold text-uppercase">Total Produk</div>
                <h3 class="fw-bold m-0 text-dark"><?php echo number_format($total_produk); ?></h3>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="border-left-color: #0d6efd;">
                <div class="stat-icon" style="background: rgba(13,110,253,0.1); color: #0d6efd;"><i class="bi bi-bell-fill"></i></div>
                <div class="text-muted small fw-bold text-uppercase">Perlu Dikirim</div>
                <h3 class="fw-bold m-0 text-dark"><?php echo number_format($pesanan_baru); ?></h3>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="border-left-color: #198754;">
                <div class="stat-icon" style="background: rgba(25,135,84,0.1); color: #198754;"><i class="bi bi-wallet2"></i></div>
                <div class="text-muted small fw-bold text-uppercase">Pendapatan Kotor</div>
                <h4 class="fw-bold m-0 text-dark">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h4>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="border-left-color: #ffc107;">
                <div class="stat-icon" style="background: rgba(255,193,7,0.1); color: #ffc107;"><i class="bi bi-star-fill"></i></div>
                <div class="text-muted small fw-bold text-uppercase">Penilaian Toko</div>
                <h3 class="fw-bold m-0 text-dark">0.0 <span class="fs-6 text-muted">/ 5.0</span></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- MENU AKSI CEPAT -->
        <div class="col-lg-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi Cepat</h5>
            <div class="row g-3">
                <div class="col-6">
                    <a href="kelola_produk.php" class="action-card">
                        <i class="bi bi-grid-fill"></i>
                        <span class="fw-bold small">Kelola<br>Produk</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="pesanan_masuk.php" class="action-card">
                        <i class="bi bi-receipt"></i>
                        <span class="fw-bold small">Pesanan<br>Masuk</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="keuangan_toko.php" class="action-card">
                        <i class="bi bi-cash-coin"></i>
                        <span class="fw-bold small">Tarik<br>Saldo</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="pengaturan_toko.php" class="action-card">
                        <i class="bi bi-gear-fill"></i>
                        <span class="fw-bold small">Pengaturan<br>Toko</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- TABEL PESANAN TERBARU -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-primary me-2"></i>Pesanan Terbaru</h5>
                <a href="pesanan_masuk.php" class="text-decoration-none small fw-bold text-primary">Lihat Semua</a>
            </div>
            
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Pembeli</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($q_pesanan_terbaru && mysqli_num_rows($q_pesanan_terbaru) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($q_pesanan_terbaru)): ?>
                                <tr>
                                    <td class="fw-bold text-muted">#ORD-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $row['nama_pembeli'] ?? 'Anonim'; ?></td>
                                    <td class="fw-bold text-dark">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if($row['status_pesanan'] == 'dikemas'): ?>
                                            <span class="badge-status badge-dikemas">Perlu Kirim</span>
                                        <?php elseif($row['status_pesanan'] == 'dikirim'): ?>
                                            <span class="badge-status badge-dikirim">Dikirim</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-selesai">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold" style="font-size: 11px;">Proses</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        Belum ada pesanan masuk.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
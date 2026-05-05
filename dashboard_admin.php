<?php
session_start();
include 'koneksi.php';

// Validasi tingkat dewa: Hanya Admin yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}

$admin_name = $_SESSION['username'];

// 1. Ambil Total Pembeli
$q_pembeli = mysqli_query($conn, "SELECT COUNT(id) FROM users WHERE role = 'pembeli'");
$tot_pembeli = mysqli_fetch_array($q_pembeli)[0];

// 2. Ambil Total Toko/Penjual
$q_penjual = mysqli_query($conn, "SELECT COUNT(id) FROM users WHERE role = 'penjual'");
$tot_penjual = mysqli_fetch_array($q_penjual)[0];

// 3. Ambil Total Produk di Platform
$q_produk = mysqli_query($conn, "SELECT COUNT(id) FROM products");
$tot_produk = mysqli_fetch_array($q_produk)[0];

// 4. Hitung GMV (Total Uang Transaksi Sukses)
$q_gmv = mysqli_query($conn, "SELECT SUM(total_bayar) FROM orders WHERE status_pesanan = 'selesai'");
$gmv = mysqli_fetch_array($q_gmv)[0] ?? 0;

// 5. Ambil 10 Transaksi Terbaru Lintas Toko
$q_transaksi = mysqli_query($conn, "
    SELECT o.*, u.username as nama_pembeli 
    FROM orders o 
    JOIN users u ON o.pembeli_id = u.id 
    ORDER BY o.id DESC LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        /* NAVBAR ADMIN (TEMA GELAP ELEGAN) */
        .admin-nav { background: linear-gradient(135deg, #1a1c20, #2c3136); padding: 15px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1030; }
        
        /* KARTU STATISTIK ADMIN */
        .stat-card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: none; height: 100%; transition: 0.3s; position: relative; overflow: hidden; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .stat-card::after { content: ''; position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; border-radius: 50%; opacity: 0.1; }
        .stat-card.c-pembeli::after { background: #0d6efd; }
        .stat-card.c-penjual::after { background: #fd7e14; }
        .stat-card.c-produk::after { background: #6f42c1; }
        .stat-card.c-gmv::after { background: #198754; }
        
        .icon-box { width: 55px; height: 55px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 15px; }
        
        /* TABEL TRANSAKSI */
        .table-card { background: #fff; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; }
        .table-custom th { background: #1a1c20; color: #fff; font-weight: normal; letter-spacing: 1px; font-size: 13px; padding: 15px; border: none; }
        .table-custom td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #eee; font-size: 14px; }
    </style>
</head>
<body>

<!-- NAVBAR KHUSUS ADMIN -->
<div class="admin-nav mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="bg-success text-white rounded p-2 me-3 fs-5"><i class="bi bi-shield-lock-fill"></i></div>
            <div>
                <h5 class="m-0 fw-bold text-white">SELLA Admin Center</h5>
                <small class="text-secondary">Welcome, <?php echo $admin_name; ?></small>
            </div>
        </div>
        <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-power me-1"></i> Shut Down</a>
    </div>
</div>

<div class="container pb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0">Platform Overview</h5>
        <button class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="location.reload();"><i class="bi bi-arrow-clockwise"></i> Refresh Data</button>
    </div>

    <!-- 4 KOTAK STATISTIK UTAMA -->
    <div class="row g-3 mb-5">
        <div class="col-6 col-md-3">
            <div class="stat-card c-pembeli border-bottom border-primary border-4">
                <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-people-fill"></i></div>
                <h6 class="text-muted fw-bold mb-1 small">Total Pembeli</h6>
                <h3 class="fw-bold m-0"><?php echo number_format($tot_pembeli, 0, ',', '.'); ?></h3>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="stat-card c-penjual border-bottom border-warning border-4">
                <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="bi bi-shop"></i></div>
                <h6 class="text-muted fw-bold mb-1 small">Toko Terdaftar</h6>
                <h3 class="fw-bold m-0"><?php echo number_format($tot_penjual, 0, ',', '.'); ?></h3>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="stat-card c-produk border-bottom border-purple border-4" style="border-color: #6f42c1 !important;">
                <div class="icon-box bg-purple bg-opacity-10" style="color: #6f42c1; background: rgba(111, 66, 193, 0.1);"><i class="bi bi-box-seam"></i></div>
                <h6 class="text-muted fw-bold mb-1 small">Produk Tersedia</h6>
                <h3 class="fw-bold m-0"><?php echo number_format($tot_produk, 0, ',', '.'); ?></h3>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card c-gmv border-bottom border-success border-4">
                <div class="icon-box bg-success bg-opacity-10 text-success"><i class="bi bi-graph-up-arrow"></i></div>
                <h6 class="text-muted fw-bold mb-1 small">Total GMV (Sukses)</h6>
                <h4 class="fw-bold m-0 text-success">Rp <?php echo number_format($gmv, 0, ',', '.'); ?></h4>
            </div>
        </div>
    </div>

    <!-- TABEL MONITORING TRANSAKSI -->
    <div class="table-card">
        <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0"><i class="bi bi-activity text-danger me-2"></i>Live Transaction Feed (Top 10)</h6>
            <span class="badge bg-dark rounded-pill">Real-time</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-custom m-0">
                <thead>
                    <tr>
                        <th width="15%">Order ID</th>
                        <th width="20%">Waktu</th>
                        <th width="20%">Pembeli</th>
                        <th width="20%">Total Nilai</th>
                        <th width="25%">Status Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($q_transaksi) > 0):
                        while($trx = mysqli_fetch_assoc($q_transaksi)): 
                    ?>
                    <tr>
                        <td class="fw-bold text-primary">#ORD-<?php echo $trx['id']; ?></td>
                        <td class="text-muted small"><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></td>
                        <td class="fw-bold"><?php echo $trx['nama_pembeli']; ?></td>
                        <td class="fw-bold text-dark">Rp <?php echo number_format($trx['total_bayar'], 0, ',', '.'); ?></td>
                        <td>
                            <?php 
                                if($trx['status_pesanan'] == 'dikemas'){
                                    echo '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-box me-1"></i> Sedang Dikemas</span>';
                                } else if($trx['status_pesanan'] == 'dikirim'){
                                    echo '<span class="badge bg-info text-white px-3 py-2 rounded-pill"><i class="bi bi-truck me-1"></i> Dalam Pengiriman</span>';
                                } else if($trx['status_pesanan'] == 'selesai'){
                                    echo '<span class="badge bg-success text-white px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Selesai</span>';
                                } else {
                                    echo '<span class="badge bg-secondary px-3 py-2 rounded-pill">'.strtoupper($trx['status_pesanan']).'</span>';
                                }
                            ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-wind fs-1 d-block mb-2"></i>
                            Belum ada transaksi di platform ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
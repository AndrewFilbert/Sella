<?php
session_start();
include 'koneksi.php';

// Proteksi tingkat tinggi: Hanya "Sultan" (Admin) yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}

$admin_name = $_SESSION['username'];

// 1. Hitung Total Pendapatan dari Biaya Admin (Pajak Transaksi Selesai)
$q_pajak = mysqli_query($conn, "SELECT SUM(biaya_admin) FROM orders WHERE status_pesanan = 'selesai'");
$total_pajak = mysqli_fetch_array($q_pajak)[0] ?? 0;

// 2. Hitung Total Uang Berputar (GMV)
$q_gmv = mysqli_query($conn, "SELECT SUM(total_bayar) FROM orders WHERE status_pesanan = 'selesai'");
$total_gmv = mysqli_fetch_array($q_gmv)[0] ?? 0;

// 3. Ambil Riwayat Transaksi Lintas Platform
$q_transaksi = mysqli_query($conn, "
    SELECT o.*, u.username as nama_pembeli 
    FROM orders o 
    JOIN users u ON o.pembeli_id = u.id 
    ORDER BY o.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sultan Dashboard - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        /* TEMA KEMILAU BLACK & GOLD */
        body { background-color: #0d0d0d; font-family: 'Courier New', Courier, monospace; color: #e6e6e6; }
        
        .gold-text { color: #d4af37 !important; }
        .gold-bg { background: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c); color: #000 !important; }
        
        .luxury-nav { background: #141414; padding: 15px 0; border-bottom: 2px solid #d4af37; box-shadow: 0 4px 20px rgba(212, 175, 55, 0.1); }
        
        .stat-card { background: linear-gradient(145deg, #1a1a1a, #222); border-radius: 15px; padding: 30px; border-left: 4px solid #d4af37; box-shadow: 5px 5px 15px #050505, -5px -5px 15px #252525; position: relative; overflow: hidden; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2); }
        .stat-card i { position: absolute; right: 20px; top: 30px; font-size: 60px; opacity: 0.1; color: #d4af37; }
        
        .table-card { background: #1a1a1a; border-radius: 15px; border: 1px solid #333; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .table-custom { color: #ccc; margin: 0; }
        .table-custom th { background: #222; color: #d4af37; font-weight: bold; padding: 18px; border-bottom: 2px solid #d4af37; letter-spacing: 1px; }
        .table-custom td { padding: 18px; border-bottom: 1px solid #333; vertical-align: middle; }
        .table-custom tr:hover { background-color: rgba(212, 175, 55, 0.05); }
        
        .badge-status { padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; letter-spacing: 1px; }
        .badge-selesai { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .badge-proses { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
        
        .btn-logout { background: transparent; color: #d4af37; border: 1px solid #d4af37; border-radius: 30px; padding: 8px 25px; transition: 0.3s; font-weight: bold; }
        .btn-logout:hover { background: #d4af37; color: #000; }
    </style>
</head>
<body>

<div class="luxury-nav mb-5">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="bi bi-gem gold-text fs-3 me-3"></i>
            <div>
                <h4 class="m-0 gold-text fw-bold">Ruang Kendali SELLA</h4>
                <small class="text-muted">Selamat datang, Yang Mulia <?php echo $admin_name; ?></small>
            </div>
        </div>
        <a href="logout.php" class="btn-logout text-decoration-none"><i class="bi bi-box-arrow-right me-2"></i>Tinggalkan Ruangan</a>
    </div>
</div>

<div class="container pb-5">

    <!-- KARTU STATISTIK KEUANGAN -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="stat-card">
                <i class="bi bi-bank"></i>
                <p class="text-muted mb-1 text-uppercase letter-spacing-1">Keuntungan Bersih (Biaya Admin)</p>
                <h2 class="gold-text fw-bold m-0">Rp <?php echo number_format($total_pajak, 0, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card border-left-0" style="border-left: 4px solid #fff;">
                <i class="bi bi-cash-stack text-white"></i>
                <p class="text-muted mb-1 text-uppercase letter-spacing-1">Total Uang Beredar (GMV)</p>
                <h2 class="text-white fw-bold m-0">Rp <?php echo number_format($total_gmv, 0, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <!-- TABEL RIWAYAT TRANSAKSI -->
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h4 class="gold-text fw-bold m-0"><i class="bi bi-journal-text me-2"></i>Rekapitulasi Transaksi</h4>
        <span class="text-muted small">Update Real-time</span>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th width="15%">ID Order</th>
                        <th width="20%">Waktu</th>
                        <th width="20%">Pembeli</th>
                        <th width="20%">Total Harga Barang</th>
                        <th width="15%" class="text-center">Biaya Admin (Profit)</th>
                        <th width="10%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($q_transaksi) > 0):
                        while($trx = mysqli_fetch_assoc($q_transaksi)): 
                    ?>
                    <tr>
                        <td class="gold-text fw-bold">#ORD-<?php echo str_pad($trx['id'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td class="small text-muted"><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></td>
                        <td class="text-white"><?php echo $trx['nama_pembeli']; ?></td>
                        <td class="fw-bold">Rp <?php echo number_format($trx['total_bayar'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <?php if($trx['biaya_admin'] > 0): ?>
                                <span class="gold-text fw-bold">+ Rp <?php echo number_format($trx['biaya_admin'], 0, ',', '.'); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                if($trx['status_pesanan'] == 'selesai'){
                                    echo '<span class="badge-status badge-selesai"><i class="bi bi-check2-all me-1"></i> Selesai</span>';
                                } else {
                                    echo '<span class="badge-status badge-proses"><i class="bi bi-hourglass-split me-1"></i> '.strtoupper($trx['status_pesanan']).'</span>';
                                }
                            ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-1 d-block mb-3 opacity-25"></i>
                            Belum ada riwayat transaksi di platform ini.
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
<?php
session_start();
include 'koneksi.php';

// Validasi khusus penjual
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];

// Logika Hapus Produk
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    // Ambil nama gambar untuk dihapus dari folder
    $q_img = mysqli_query($conn, "SELECT gambar FROM products WHERE id = '$id_hapus' AND penjual_id = '$penjual_id'");
    if ($img = mysqli_fetch_assoc($q_img)) {
        @unlink('uploads/' . $img['gambar']); 
    }
    // Hapus dari database 
    mysqli_query($conn, "DELETE FROM products WHERE id = '$id_hapus' AND penjual_id = '$penjual_id'");
    echo "<script>alert('Produk berhasil dihapus dari etalase!'); window.location='dashboard_penjual.php';</script>";
    exit;
}

// 1. Ambil Info Toko (Username & Saldo)
$q_toko = mysqli_query($conn, "SELECT username, saldo FROM users WHERE id = '$penjual_id'");
$toko = mysqli_fetch_assoc($q_toko);

// 2. Hitung Total Produk di Etalase
$q_tot_produk = mysqli_query($conn, "SELECT COUNT(id) FROM products WHERE penjual_id = '$penjual_id'");
$tot_produk = mysqli_fetch_array($q_tot_produk)[0];

// 3. Hitung Pesanan Baru (Perlu Dikemas)
$q_pesanan = mysqli_query($conn, "
    SELECT COUNT(DISTINCT o.id) FROM orders o 
    JOIN order_details od ON o.id = od.order_id 
    JOIN products p ON od.produk_id = p.id 
    WHERE p.penjual_id = '$penjual_id' AND o.status_pesanan = 'dikemas'
");
$pesanan_baru = mysqli_fetch_array($q_pesanan)[0];

// 4. Ambil Daftar Produk Toko Ini
$q_produk = mysqli_query($conn, "SELECT * FROM products WHERE penjual_id = '$penjual_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Toko - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        /* NAVBAR PENJUAL */
        .seller-nav { background: #212529; padding: 15px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .seller-nav a { color: #fff; text-decoration: none; font-weight: bold; }
        
        /* KOTAK STATISTIK */
        .stat-card { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: none; height: 100%; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.08); }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; }
        
        /* TABEL PRODUK */
        .table-card { background: #fff; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; }
        .table-custom th { background: #f8f9fa; color: #555; font-weight: bold; text-transform: uppercase; font-size: 13px; padding: 15px; border-bottom: 2px solid #eee; }
        .table-custom td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #eee; font-size: 14px; }
        .prod-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>

<div class="seller-nav mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="fs-5 fw-bold text-white"><i class="bi bi-shop me-2 text-success"></i>Toko <?php echo $toko['username']; ?></span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
</div>

<div class="container pb-5">
    
    <!-- BARIS STATISTIK -->
    <div class="row g-3 mb-4">
        <!-- Saldo Toko -->
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box bg-success bg-opacity-10 text-success"><i class="bi bi-wallet2"></i></div>
                <h6 class="text-muted fw-bold mb-1">Saldo Pendapatan</h6>
                <h3 class="fw-bold mb-0">Rp <?php echo number_format($toko['saldo'], 0, ',', '.'); ?></h3>
                <a href="#" class="btn btn-success btn-sm mt-3 rounded-pill fw-bold w-100">Tarik Dana</a>
            </div>
        </div>
        
        <!-- Pesanan Baru -->
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="bi bi-box-seam"></i></div>
                <h6 class="text-muted fw-bold mb-1">Pesanan Perlu Dikemas</h6>
                <h3 class="fw-bold mb-0"><?php echo $pesanan_baru; ?> <span class="fs-6 text-muted fw-normal">pesanan</span></h3>
                <a href="pesanan_masuk.php" class="btn btn-warning btn-sm mt-3 rounded-pill fw-bold w-100 text-dark">Kelola Pesanan</a>
            </div>
        </div>
        
        <!-- Total Produk -->
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-grid"></i></div>
                <h6 class="text-muted fw-bold mb-1">Total Produk Aktif</h6>
                <h3 class="fw-bold mb-0"><?php echo $tot_produk; ?> <span class="fs-6 text-muted fw-normal">produk</span></h3>
                <a href="tambah_produk.php" class="btn btn-primary btn-sm mt-3 rounded-pill fw-bold w-100">Tambah Produk Baru</a>
            </div>
        </div>
    </div>

    <!-- TABEL ETALASE PRODUK -->
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
            <h5 class="fw-bold m-0">Etalase Produk Saya</h5>
            <a href="tambah_produk.php" class="btn btn-dark btn-sm rounded-pill fw-bold px-3"><i class="bi bi-plus-lg"></i> Tambah</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-custom m-0">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="40%">Info Produk</th>
                        <th width="15%">Kategori</th>
                        <th width="15%">Harga</th>
                        <th width="10%">Stok</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($q_produk) > 0):
                        $no = 1;
                        while($p = mysqli_fetch_assoc($q_produk)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="uploads/<?php echo $p['gambar']; ?>" class="prod-img shadow-sm">
                                <div class="ms-3">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 250px;"><?php echo $p['nama_produk']; ?></div>
                                    <div class="small text-muted">ID: PROD-<?php echo $p['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        <!-- PENAMBAHAN '?? Lainnya' AGAR TIDAK ERROR JIKA KATEGORI KOSONG -->
                        <td><span class="badge bg-secondary text-capitalize px-2 py-1"><?php echo $p['kategori'] ?? 'Lainnya'; ?></span></td>
                        <td class="fw-bold text-success">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                        <td class="fw-bold"><?php echo $p['stok']; ?></td>
                        <td class="text-center">
                            <a href="?hapus=<?php echo $p['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('Yakin ingin menghapus produk ini?');">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-shop fs-1 d-block mb-2"></i>
                            Toko Anda masih kosong. Mulai tambahkan produk pertama Anda!
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
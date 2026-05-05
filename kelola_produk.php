<?php
session_start();
include 'koneksi.php';

// Proteksi tingkat tinggi: Hanya penjual yang boleh masuk!
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];

// PROSES HAPUS PRODUK
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    
    // Cari nama file gambar di database agar bisa dihapus dari folder server
    $q_gambar = mysqli_query($conn, "SELECT gambar FROM products WHERE id = '$id_hapus' AND penjual_id = '$penjual_id'");
    if (mysqli_num_rows($q_gambar) > 0) {
        $data_gambar = mysqli_fetch_assoc($q_gambar);
        $file_gambar = "uploads/" . $data_gambar['gambar'];
        
        // Hapus file fisik gambar jika ada
        if (file_exists($file_gambar) && !empty($data_gambar['gambar'])) {
            unlink($file_gambar);
        }
        
        // Hapus data dari database
        mysqli_query($conn, "DELETE FROM products WHERE id = '$id_hapus' AND penjual_id = '$penjual_id'");
        
        // Refresh halaman dengan pesan sukses
        header("Location: kelola_produk.php?pesan=sukses_hapus");
        exit;
    }
}

// AMBIL SEMUA DATA PRODUK MILIK TOKO INI
$q_produk = mysqli_query($conn, "SELECT * FROM products WHERE penjual_id = '$penjual_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Produk - SELLA Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Courier New', Courier, monospace; color: #333; padding-bottom: 50px; }
        
        /* HEADER */
        .seller-nav { background: #fff; padding: 15px 0; border-bottom: 3px solid #fa591d; box-shadow: 0 2px 15px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1030; }
        .seller-nav a { color: #333; font-size: 20px; text-decoration: none; font-weight: bold; }
        
        .page-title-box { background: linear-gradient(135deg, #fa591d, #ff7a45); border-radius: 15px; color: white; padding: 25px; margin-top: 30px; margin-bottom: 30px; box-shadow: 0 10px 20px rgba(250, 89, 29, 0.2); display: flex; justify-content: space-between; align-items: center; }
        
        .table-container { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .table thead th { background-color: #f8f9fa; color: #555; border-bottom: 2px solid #eee; font-weight: bold; text-transform: uppercase; font-size: 13px; }
        .table tbody td { vertical-align: middle; border-bottom: 1px solid #f1f1f1; }
        
        .img-thumbnail-custom { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
        
        .btn-orange { background: #fa591d; color: white; font-weight: bold; border-radius: 30px; padding: 10px 25px; border: none; transition: 0.3s; box-shadow: 0 4px 10px rgba(250,89,29,0.3); text-decoration: none; display: inline-block; }
        .btn-orange:hover { background: #e04b14; color: white; transform: translateY(-2px); }
        
        .badge-stok { font-size: 12px; padding: 5px 10px; border-radius: 20px; }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="seller-nav">
    <div class="container d-flex align-items-center">
        <a href="dashboard_penjual.php" class="me-3"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Kelola Produk Etalase</h5>
    </div>
</div>

<div class="container">
    
    <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses_hapus'): ?>
        <div class="alert alert-success mt-4 fw-bold rounded-3 shadow-sm border-0"><i class="bi bi-trash-fill me-2"></i>Produk berhasil dihapus dari etalase!</div>
    <?php endif; ?>

    <div class="page-title-box flex-column flex-md-row text-center text-md-start">
        <div class="mb-3 mb-md-0">
            <h4 class="fw-bold mb-1"><i class="bi bi-grid-fill me-2"></i>Daftar Produk Anda</h4>
            <p class="m-0 small opacity-75">Kelola stok, harga, dan etalase toko Anda dari sini.</p>
        </div>
        <!-- TOMBOL MENUJU HALAMAN TAMBAH PRODUK -->
        <a href="tambah_produk.php" class="btn btn-light fw-bold text-dark rounded-pill shadow-sm px-4 py-2">
            <i class="bi bi-plus-circle-fill text-danger me-1"></i> Tambah Produk Baru
        </a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="10%">Foto</th>
                        <th width="30%">Nama Produk</th>
                        <th width="20%">Kategori</th>
                        <th width="15%">Harga</th>
                        <th width="10%">Stok</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($q_produk) > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($q_produk)): ?>
                        <tr>
                            <td>
                                <?php if(!empty($p['gambar'])): ?>
                                    <img src="uploads/<?php echo $p['gambar']; ?>" class="img-thumbnail-custom" alt="Foto">
                                <?php else: ?>
                                    <div class="img-thumbnail-custom bg-light d-flex align-items-center justify-content-center text-muted">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <?php echo $p['nama_produk']; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill fw-normal"><?php echo ucfirst($p['kategori']); ?></span>
                            </td>
                            <td class="fw-bold text-success">
                                Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <?php if($p['stok'] > 5): ?>
                                    <span class="badge-stok bg-success bg-opacity-10 text-success fw-bold"><?php echo $p['stok']; ?> pcs</span>
                                <?php elseif($p['stok'] > 0): ?>
                                    <span class="badge-stok bg-warning bg-opacity-10 text-warning fw-bold"><?php echo $p['stok']; ?> pcs</span>
                                <?php else: ?>
                                    <span class="badge-stok bg-danger bg-opacity-10 text-danger fw-bold">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="kelola_produk.php?hapus=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill fw-bold" onclick="return confirm('Yakin ingin menghapus produk ini secara permanen?');">
                                    <i class="bi bi-trash3-fill"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-25"></i>
                                <h5 class="fw-bold text-dark">Toko Anda Masih Kosong!</h5>
                                <p class="small">Belum ada produk yang dijual. Ayo tambahkan produk pertama Anda!</p>
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
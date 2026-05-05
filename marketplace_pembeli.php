<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Logika Filter & Pencarian
$where_clauses = [];

if (isset($_GET['cari']) && $_GET['cari'] != '') {
    $cari = mysqli_real_escape_string($conn, $_GET['cari']);
    $where_clauses[] = "p.nama_produk LIKE '%$cari%'";
}
if (isset($_GET['kategori']) && $_GET['kategori'] != '') {
    $kategori = mysqli_real_escape_string($conn, $_GET['kategori']);
    $where_clauses[] = "p.kategori = '$kategori'";
}
if (isset($_GET['min_harga']) && $_GET['min_harga'] != '') {
    $min = (int)$_GET['min_harga'];
    $where_clauses[] = "p.harga >= $min";
}
if (isset($_GET['max_harga']) && $_GET['max_harga'] != '') {
    $max = (int)$_GET['max_harga'];
    $where_clauses[] = "p.harga <= $max";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Ambil Produk dari Database berdasarkan Filter
$query = "SELECT p.*, u.username as nama_toko FROM products p JOIN users u ON p.penjual_id = u.id $where_sql ORDER BY p.id DESC";
$produk = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SELLA - Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        .top-header { background: linear-gradient(135deg, #118a44, #18b35a); padding: 15px 0; color: white; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-box { border-radius: 20px 0 0 20px; border: none; padding-left: 20px; }
        .btn-search { border-radius: 0 20px 20px 0; background-color: #fa591d; color: white; border: none; padding: 0 20px; font-weight: bold; }
        .btn-filter { border-radius: 10px; background-color: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.5); font-weight: bold; }
        
        .product-card { border: none; border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-decoration: none; color: inherit; display: block; height: 100%; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); color: inherit; }
        .img-wrapper { height: 180px; width: 100%; overflow: hidden; position: relative; }
        .img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .badge-stok { position: absolute; top: 10px; right: 10px; font-size: 11px; padding: 5px 10px; border-radius: 20px; font-weight: bold; }
        
        .bottom-nav { background: #fff; position: fixed; bottom: 0; width: 100%; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 1030; display: flex; padding: 10px 0; }
        .nav-item { flex: 1; text-align: center; color: #888; text-decoration: none; font-size: 12px; display: flex; flex-direction: column; align-items: center; }
        .nav-item.active { color: #118a44; font-weight: 600; }
        .nav-item i { font-size: 22px; margin-bottom: 2px; }
        
        @media (max-width: 767px) { body { padding-bottom: 80px; } }
    </style>
</head>
<body>

<!-- 1. HEADER & FORM PENCARIAN -->
<div class="top-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0"><i class="bi bi-bag-heart-fill"></i> SELLA</h4>
            <div class="d-flex gap-3">
                <a href="wishlist.php" class="text-white fs-5 d-none d-md-block" title="Favorit"><i class="bi bi-heart"></i></a>
                
                <!-- INI DIA TOMBOL DOMPET UNTUK PC! -->
                <a href="dompet_pembeli.php" class="text-white fs-5 d-none d-md-block" title="Dompet Saya"><i class="bi bi-wallet2"></i></a>
                
                <a href="keranjang.php" class="text-white fs-5" title="Keranjang"><i class="bi bi-cart3"></i></a>
                <a href="pesanan_saya.php" class="text-white fs-5 d-none d-md-block" title="Pesanan Saya"><i class="bi bi-bell"></i></a>
                <a href="logout.php" class="text-white fs-5 d-none d-md-block" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <!-- Form Cari & Tombol Filter -->
        <form action="" method="GET" class="d-flex gap-2">
            <div class="input-group flex-grow-1">
                <input type="text" name="cari" class="form-control search-box shadow-sm" placeholder="Cari sepatu, baju..." value="<?php echo isset($_GET['cari']) ? $_GET['cari'] : ''; ?>">
                <button type="submit" class="btn btn-search shadow-sm"><i class="bi bi-search"></i></button>
            </div>
            <button type="button" class="btn btn-filter shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-funnel-fill"></i>
            </button>
        </form>
    </div>
</div>

<!-- MODAL FILTER PENCARIAN -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 15px; font-family: 'Courier New', Courier, monospace;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Filter Pencarian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="GET">
                    <!-- Bawa nilai cari jika ada -->
                    <?php if(isset($_GET['cari'])): ?>
                        <input type="hidden" name="cari" value="<?php echo $_GET['cari']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-muted">Kategori</label>
                        <select name="kategori" class="form-select border-success">
                            <option value="">Semua Kategori</option>
                            <option value="pakaian" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'pakaian') ? 'selected' : ''; ?>>Pakaian</option>
                            <option value="sepatu" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'sepatu') ? 'selected' : ''; ?>>Sepatu</option>
                            <option value="elektronik" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'elektronik') ? 'selected' : ''; ?>>Elektronik</option>
                            <option value="makanan" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'makanan') ? 'selected' : ''; ?>>Makanan & Minuman</option>
                        </select>
                    </div>

                    <label class="fw-bold mb-2 small text-muted">Rentang Harga (Rp)</label>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <input type="number" name="min_harga" class="form-control" placeholder="Min" value="<?php echo isset($_GET['min_harga']) ? $_GET['min_harga'] : ''; ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" name="max_harga" class="form-control" placeholder="Max" value="<?php echo isset($_GET['max_harga']) ? $_GET['max_harga'] : ''; ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="marketplace_pembeli.php" class="btn btn-light border w-50 fw-bold rounded-pill">Reset</a>
                        <button type="submit" class="btn btn-success w-50 fw-bold rounded-pill shadow-sm">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. DAFTAR PRODUK -->
<div class="container mt-4 mb-5">
    <?php if(count($where_clauses) > 0): ?>
        <div class="mb-3 small text-muted"><i class="bi bi-funnel text-success"></i> Menampilkan hasil filter.</div>
    <?php endif; ?>

    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3">
        <?php if(mysqli_num_rows($produk) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                <div class="col">
                    <a href="detail_produk.php?id=<?php echo $p['id']; ?>" class="product-card">
                        <div class="img-wrapper">
                            <img src="uploads/<?php echo $p['gambar']; ?>" alt="<?php echo $p['nama_produk']; ?>" loading="lazy">
                            <?php if($p['stok'] == 0): ?>
                                <span class="badge-stok bg-dark text-white opacity-75">Stok Habis</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-3 d-flex flex-column" style="height: 120px;">
                            <div class="text-dark" style="font-size: 13px; font-weight: 600; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?php echo $p['nama_produk']; ?></div>
                            <div class="mt-auto">
                                <div class="text-danger fw-bold" style="font-size: 15px;">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></div>
                                <div class="text-muted d-flex align-items-center mt-1" style="font-size: 11px;">
                                    <i class="bi bi-shop me-1 text-success"></i> <span class="text-truncate"><?php echo $p['nama_toko']; ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search text-muted" style="font-size: 50px;"></i>
                <h5 class="mt-3 text-muted fw-bold">Barang tidak ditemukan</h5>
                <p class="small text-muted">Coba gunakan kata kunci atau filter lain.</p>
                <a href="marketplace_pembeli.php" class="btn btn-outline-success btn-sm rounded-pill px-4 mt-2">Hapus Filter</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 3. BOTTOM NAV (Khusus Mobile) -->
<div class="bottom-nav d-md-none">
    <a href="marketplace_pembeli.php" class="nav-item active"><i class="bi bi-house-door-fill"></i><span>Beranda</span></a>
    <a href="wishlist.php" class="nav-item"><i class="bi bi-heart"></i><span>Favorit</span></a>
    <a href="keranjang.php" class="nav-item"><i class="bi bi-cart3"></i><span>Keranjang</span></a>
    <a href="pesanan_saya.php" class="nav-item"><i class="bi bi-receipt"></i><span>Pesanan</span></a>
    <a href="dompet_pembeli.php" class="nav-item"><i class="bi bi-wallet2"></i><span>Dompet</span></a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
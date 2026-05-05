<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$pembeli_id = $_SESSION['id'];
$query_sql = "SELECT p.*, u.username as nama_toko FROM wishlists w JOIN products p ON w.produk_id = p.id JOIN users u ON p.penjual_id = u.id WHERE w.pembeli_id = '$pembeli_id' ORDER BY w.id DESC";
$produk = mysqli_query($conn, $query_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Saya - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .product-card { border: none; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .img-wrapper { height: 160px; width: 100%; overflow: hidden; }
        .img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>

<div class="container py-4">
    <a href="marketplace_pembeli.php" class="btn btn-light shadow-sm mb-4"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-4"><i class="bi bi-heart-fill text-danger"></i> Barang Favorit Saya</h4>

    <div class="row row-cols-2 row-cols-md-4 g-3">
        <?php if(mysqli_num_rows($produk) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                <div class="col">
                    <a href="detail_produk.php?id=<?php echo $p['id']; ?>" class="text-decoration-none">
                        <div class="product-card">
                            <div class="img-wrapper">
                                <img src="uploads/<?php echo $p['gambar']; ?>" alt="<?php echo $p['nama_produk']; ?>">
                            </div>
                            <div class="card-body p-3">
                                <div class="text-dark" style="font-size: 14px; font-weight:600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo $p['nama_produk']; ?></div>
                                <div class="text-danger fw-bold mt-1">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-heartbreak fs-1 text-muted"></i>
                <p class="mt-2 text-muted">Belum ada produk favorit.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
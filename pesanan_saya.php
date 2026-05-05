<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$pembeli_id = $_SESSION['id'];

// Logika tombol "Pesanan Diterima"
if (isset($_POST['terima_pesanan'])) {
    $order_id = (int)$_POST['order_id'];
    mysqli_query($conn, "UPDATE orders SET status_pesanan = 'selesai' WHERE id = '$order_id' AND pembeli_id = '$pembeli_id'");
    echo "<script>alert('Terima kasih! Pesanan telah diselesaikan.'); window.location='pesanan_saya.php';</script>";
    exit;
}

$q_orders = mysqli_query($conn, "SELECT * FROM orders WHERE pembeli_id = '$pembeli_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        /* NAVBAR RESPONSIVE */
        .top-nav { background: #fff; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .top-nav a { color: #333; font-size: 24px; text-decoration: none; margin-right: 15px; }
        
        /* CARD PESANAN */
        .order-container { max-width: 800px; margin: 0 auto; }
        .order-card { background: #fff; margin-bottom: 20px; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border: 1px solid #eee; }
        .order-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px dashed #eee; margin-bottom: 15px; }
        .badge-status { font-size: 12px; padding: 6px 12px; border-radius: 20px; font-weight: bold; }
        
        /* PRODUK ITEM */
        .product-item { display: flex; margin-bottom: 15px; align-items: center; }
        .product-item img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; margin-right: 15px; }
        
        .order-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px dashed #eee; margin-top: 10px; }
        
        /* BOTTOM NAV (MOBILE ONLY) */
        .bottom-nav { background: #fff; position: fixed; bottom: 0; width: 100%; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 1030; display: flex; padding: 10px 0; }
        .nav-item { flex: 1; text-align: center; color: #888; text-decoration: none; font-size: 12px; display: flex; flex-direction: column; align-items: center; }
        .nav-item.active { color: #118a44; font-weight: 600; }
        .nav-item i { font-size: 22px; margin-bottom: 2px; }
        
        @media (min-width: 768px) {
            .mobile-only { display: none !important; }
        }
        @media (max-width: 767px) {
            body { padding-bottom: 80px; }
        }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="container d-flex align-items-center">
        <a href="marketplace_pembeli.php"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Daftar Pesanan</h5>
    </div>
</div>

<div class="container order-container mt-4 mb-5">
    <?php if (mysqli_num_rows($q_orders) > 0): ?>
        <?php while ($order = mysqli_fetch_assoc($q_orders)): ?>
            <div class="order-card">
                
                <div class="order-header">
                    <div>
                        <span class="fw-bold text-dark"><i class="bi bi-bag-check text-success me-2"></i>Belanja</span>
                        <span class="text-muted ms-2 small d-none d-md-inline">| <?php echo date('d M Y', strtotime($order['tanggal_transaksi'])); ?></span>
                    </div>
                    <?php 
                        if ($order['status_pesanan'] == 'dikemas') {
                            echo '<span class="badge-status bg-warning text-dark">Sedang Dikemas</span>';
                        } elseif ($order['status_pesanan'] == 'dikirim') {
                            echo '<span class="badge-status bg-info text-white">Sedang Dikirim</span>';
                        } else {
                            echo '<span class="badge-status bg-success text-white">Selesai</span>';
                        }
                    ?>
                </div>

                <?php
                $oid = $order['id'];
                $q_detail = mysqli_query($conn, "SELECT od.*, p.nama_produk, p.gambar FROM order_details od JOIN products p ON od.produk_id = p.id WHERE od.order_id = '$oid'");
                while ($detail = mysqli_fetch_assoc($q_detail)):
                ?>
                <div class="product-item">
                    <img src="uploads/<?php echo $detail['gambar']; ?>">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-truncate" style="font-size: 15px; max-width: 80%;"><?php echo $detail['nama_produk']; ?></div>
                        <div class="text-muted small">x<?php echo $detail['jumlah']; ?></div>
                    </div>
                    <div class="fw-bold text-dark fs-6">Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></div>
                </div>
                <?php endwhile; ?>

                <?php if ($order['status_pesanan'] == 'dikirim' && !empty($order['no_resi'])): ?>
                <div class="bg-light p-2 rounded mb-3 small border">
                    <i class="bi bi-truck text-success me-2"></i>No. Resi Pengiriman: <strong class="user-select-all"><?php echo $order['no_resi']; ?></strong>
                </div>
                <?php endif; ?>

                <div class="order-footer">
                    <div>
                        <div class="text-muted small">Total Pesanan</div>
                        <div class="fw-bold text-danger fs-5">Rp <?php echo number_format($order['total_bayar'], 0, ',', '.'); ?></div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <?php if ($order['status_pesanan'] == 'dikirim'): ?>
                            <form method="POST" class="m-0">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="terima_pesanan" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm">Pesanan Diterima</button>
                            </form>
                        <?php elseif ($order['status_pesanan'] == 'selesai'): 
                                // Cek apakah order ini sudah diulas
                                $cek_ulasan = mysqli_query($conn, "SELECT id FROM reviews WHERE order_id = '$oid' LIMIT 1");
                                if(mysqli_num_rows($cek_ulasan) > 0):
                        ?>
                                <button class="btn btn-light border fw-bold px-4 rounded-pill" disabled>Ulasan Dikirim</button>
                        <?php else: ?>
                                <a href="tulis_ulasan.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-success fw-bold px-4 rounded-pill shadow-sm">Beri Ulasan</a>
                        <?php endif; ?>
                        
                        <?php else: ?>
                            <button class="btn btn-secondary fw-bold px-4 rounded-pill" disabled>Hubungi Penjual</button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-5 mt-5">
            <i class="bi bi-receipt text-muted" style="font-size: 80px;"></i>
            <h5 class="mt-3 fw-bold text-muted">Belum ada pesanan</h5>
            <p class="text-muted small">Semua transaksi Anda akan ditampilkan di sini.</p>
        </div>
    <?php endif; ?>
</div>

<!-- BOTTOM NAVIGATION BAR (Khusus HP) -->
<div class="bottom-nav mobile-only">
    <a href="marketplace_pembeli.php" class="nav-item">
        <i class="bi bi-house-door"></i><span>Beranda</span>
    </a>
    <a href="keranjang.php" class="nav-item">
        <i class="bi bi-cart3"></i><span>Keranjang</span>
    </a>
    <a href="pesanan_saya.php" class="nav-item active">
        <i class="bi bi-receipt"></i><span>Pesanan</span>
    </a>
    <a href="dompet_pembeli.php" class="nav-item">
        <i class="bi bi-wallet2"></i><span>Dompet</span>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
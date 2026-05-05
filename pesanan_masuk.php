<?php
session_start();
include 'koneksi.php';

// Validasi akses khusus Penjual
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];

// Logika untuk memproses pengiriman (Input Resi)
if (isset($_POST['kirim_pesanan'])) {
    $order_id = (int)$_POST['order_id'];
    $no_resi = mysqli_real_escape_string($conn, $_POST['no_resi']);
    
    // Update status pesanan menjadi dikirim dan simpan no resi
    mysqli_query($conn, "UPDATE orders SET status_pesanan = 'dikirim', no_resi = '$no_resi' WHERE id = '$order_id'");
    echo "<script>alert('Resi berhasil disimpan! Status pesanan kini menjadi Sedang Dikirim.'); window.location='pesanan_masuk.php';</script>";
    exit;
}

// Ambil semua pesanan yang berisi produk milik penjual ini
$q_orders = mysqli_query($conn, "
    SELECT DISTINCT o.*, u.username as nama_pembeli, u.alamat_lengkap, u.no_hp 
    FROM orders o 
    JOIN order_details od ON o.id = od.order_id 
    JOIN products p ON od.produk_id = p.id 
    JOIN users u ON o.pembeli_id = u.id
    WHERE p.penjual_id = '$penjual_id' 
    ORDER BY o.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Masuk - SELLA Toko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        /* NAVBAR PENJUAL */
        .seller-nav { background: #212529; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .seller-nav a { color: #fff; text-decoration: none; font-weight: bold; }
        
        /* CARD PESANAN */
        .order-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; border: 1px solid #eee; }
        .order-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .order-body { padding: 20px; }
        
        /* ITEM PRODUK */
        .product-item { display: flex; align-items: center; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px dashed #eee; }
        .product-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .product-item img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; margin-right: 15px; }
        
        /* RESPONSIVE LAYOUT */
        @media (max-width: 767px) {
            .order-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .buyer-info { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #eee; }
        }
    </style>
</head>
<body>

<div class="seller-nav">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="dashboard_penjual.php" class="fs-5"><i class="bi bi-arrow-left me-2"></i>Toko Saya</a>
        <span class="badge bg-success px-3 py-2 fs-6">Kelola Pesanan</span>
    </div>
</div>

<div class="container mt-4 mb-5">
    
    <ul class="nav nav-pills mb-4 gap-2" id="orderTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold" id="baru-tab" data-bs-toggle="tab" data-bs-target="#baru" type="button" role="tab">Perlu Dikirim</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold" id="dikirim-tab" data-bs-toggle="tab" data-bs-target="#dikirim" type="button" role="tab">Sedang Dikirim</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold" id="selesai-tab" data-bs-toggle="tab" data-bs-target="#selesai" type="button" role="tab">Selesai</button>
        </li>
    </ul>

    <div class="tab-content" id="orderTabContent">
        
        <!-- TAB: PERLU DIKIRIM (DIKEMAS) -->
        <div class="tab-pane fade show active" id="baru" role="tabpanel">
            <?php 
            mysqli_data_seek($q_orders, 0);
            $has_baru = false;
            while ($order = mysqli_fetch_assoc($q_orders)): 
                if ($order['status_pesanan'] != 'dikemas') continue;
                $has_baru = true;
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="fw-bold">Order ID: #ORD-<?php echo $order['id']; ?></span><br>
                        <span class="small text-muted"><i class="bi bi-clock"></i> <?php echo date('d M Y, H:i', strtotime($order['tanggal_transaksi'])); ?></span>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-box-seam me-1"></i> Perlu Dikemas</span>
                </div>
                <div class="order-body row">
                    <div class="col-md-7">
                        <?php
                        $oid = $order['id'];
                        $q_detail = mysqli_query($conn, "SELECT od.*, p.nama_produk, p.gambar FROM order_details od JOIN products p ON od.produk_id = p.id WHERE od.order_id = '$oid' AND p.penjual_id = '$penjual_id'");
                        while ($detail = mysqli_fetch_assoc($q_detail)):
                        ?>
                        <div class="product-item">
                            <img src="uploads/<?php echo $detail['gambar']; ?>">
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size: 14px;"><?php echo $detail['nama_produk']; ?></div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-muted small">x<?php echo $detail['jumlah']; ?></span>
                                    <span class="fw-bold text-success">Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="col-md-5 buyer-info border-start px-md-4">
                        <h6 class="fw-bold"><i class="bi bi-person-lines-fill text-primary me-2"></i>Info Pembeli</h6>
                        <div class="small mb-1 fw-bold"><?php echo $order['nama_pembeli']; ?></div>
                        <div class="small text-muted mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> <?php echo $order['alamat_lengkap']; ?></div>
                        
                        <div class="bg-light p-3 rounded mt-auto border">
                            <form method="POST" action="">
                                <label class="small fw-bold mb-2">Input Nomor Resi Pengiriman:</label>
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <div class="input-group">
                                    <input type="text" name="no_resi" class="form-control form-control-sm" placeholder="Contoh: JP1234567890" required>
                                    <button type="submit" name="kirim_pesanan" class="btn btn-success btn-sm fw-bold">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; if (!$has_baru) echo "<div class='text-center py-5 text-muted'><i class='bi bi-inbox fs-1'></i><p>Tidak ada pesanan baru.</p></div>"; ?>
        </div>

        <!-- TAB: SEDANG DIKIRIM -->
        <div class="tab-pane fade" id="dikirim" role="tabpanel">
            <?php 
            mysqli_data_seek($q_orders, 0);
            $has_dikirim = false;
            while ($order = mysqli_fetch_assoc($q_orders)): 
                if ($order['status_pesanan'] != 'dikirim') continue;
                $has_dikirim = true;
            ?>
            <div class="order-card opacity-75">
                <div class="order-header">
                    <div>
                        <span class="fw-bold">Order ID: #ORD-<?php echo $order['id']; ?></span>
                    </div>
                    <span class="badge bg-info text-white px-3 py-2 rounded-pill"><i class="bi bi-truck me-1"></i> Sedang Dikirim</span>
                </div>
                <div class="order-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark mb-1"><?php echo $order['nama_pembeli']; ?></div>
                        <div class="small text-muted">No. Resi: <strong class="text-dark"><?php echo $order['no_resi']; ?></strong></div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted mb-1">Total Belanja</div>
                        <div class="fw-bold text-success fs-5">Rp <?php echo number_format($order['total_bayar'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
            <?php endwhile; if (!$has_dikirim) echo "<div class='text-center py-5 text-muted'><p>Tidak ada pesanan yang sedang dikirim.</p></div>"; ?>
        </div>

        <!-- TAB: SELESAI -->
        <div class="tab-pane fade" id="selesai" role="tabpanel">
            <?php 
            mysqli_data_seek($q_orders, 0);
            $has_selesai = false;
            while ($order = mysqli_fetch_assoc($q_orders)): 
                if ($order['status_pesanan'] != 'selesai') continue;
                $has_selesai = true;
            ?>
            <div class="order-card bg-light">
                <div class="order-header bg-transparent border-0 pb-0">
                    <div>
                        <span class="fw-bold text-muted">Order ID: #ORD-<?php echo $order['id']; ?></span>
                    </div>
                    <span class="badge bg-success text-white px-3 py-2 rounded-pill"><i class="bi bi-check2-circle me-1"></i> Selesai</span>
                </div>
                <div class="order-body">
                    <div class="d-flex justify-content-between align-items-end border-top pt-3 mt-2">
                        <div class="small text-muted">Pesanan telah diterima oleh <strong><?php echo $order['nama_pembeli']; ?></strong>. Dana diteruskan ke Dompet Toko.</div>
                        <div class="fw-bold text-success">Rp <?php echo number_format($order['total_bayar'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
            <?php endwhile; if (!$has_selesai) echo "<div class='text-center py-5 text-muted'><p>Belum ada pesanan yang selesai.</p></div>"; ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
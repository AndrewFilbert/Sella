<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$pembeli_id = $_SESSION['id'];
$cart_empty = !isset($_SESSION['cart']) || empty($_SESSION['cart']);

if ($cart_empty) {
    $q_rek = mysqli_query($conn, "SELECT p.*, u.username as nama_toko FROM products p JOIN users u ON p.penjual_id = u.id ORDER BY RAND() LIMIT 4");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Keranjang - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* NAVIGASI ATAS */
        .top-nav { background: #fff; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .top-nav a { color: #333; font-size: 24px; text-decoration: none; margin-right: 15px; }
        
        /* KOMPONEN KARTU */
        .section-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 15px; }
        .product-item { display: flex; align-items: center; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px dashed #eee; }
        .product-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 15px; }
        .custom-checkbox { width: 22px; height: 22px; cursor: pointer; accent-color: #118a44; margin-right: 15px; }
        
        /* KOTAK RINGKASAN (UNTUK PC) */
        .summary-box { position: sticky; top: 80px; }
        .btn-checkout { background: linear-gradient(135deg, #118a44, #18b35a); color: #fff; width: 100%; border-radius: 12px; font-weight: bold; border: none; font-size: 16px; padding: 12px; }
        .btn-checkout:disabled { background: #ccc; color: #666; }

        /* REKOMENDASI */
        .rek-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; height: 100%; text-decoration: none; color: #333;}
        .rek-card img { width: 100%; height: 150px; object-fit: cover; }

        /* BOTTOM BAR (HANYA TAMPIL DI HP) */
        .bottom-bar { position: fixed; bottom: 0; width: 100%; background: #fff; padding: 15px; box-shadow: 0 -4px 15px rgba(0,0,0,0.1); z-index: 1000; display: flex; align-items: center; justify-content: space-between; }
        
        /* HIDE PADA PC */
        @media (min-width: 768px) {
            .mobile-only { display: none !important; }
        }
        /* HIDE PADA HP */
        @media (max-width: 767px) {
            .desktop-only { display: none !important; }
            body { padding-bottom: 100px; } /* Jarak untuk bottom bar HP */
        }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="container d-flex align-items-center">
        <a href="marketplace_pembeli.php"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Keranjang Saya</h5>
    </div>
</div>

<div class="container mt-4">
    <?php if ($cart_empty): ?>
        <!-- JIKA KERANJANG KOSONG -->
        <div class="text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 80px;"></i>
            <h5 class="mt-3 fw-bold text-muted">Keranjangmu kosong!</h5>
            <p class="text-muted small">Cari barang impianmu dan masukkan ke sini.</p>
            <a href="marketplace_pembeli.php" class="btn btn-outline-success rounded-pill px-4 mt-2 fw-bold">Belanja Sekarang</a>
        </div>

        <h6 class="fw-bold mb-3 text-danger mt-4"><i class="bi bi-fire"></i> Rekomendasi Spesial Untukmu</h6>
        <!-- Grid responsif: 2 kolom di HP, 4 kolom di PC -->
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-5">
            <?php while ($p = mysqli_fetch_assoc($q_rek)): ?>
            <div class="col">
                <a href="detail_produk.php?id=<?php echo $p['id']; ?>" class="rek-card">
                    <img src="uploads/<?php echo $p['gambar']; ?>">
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <div style="font-size: 14px; font-weight: 600; line-height: 1.4; height: 40px; overflow: hidden;"><?php echo $p['nama_produk']; ?></div>
                        <div class="text-danger fw-bold mt-auto pt-2">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <!-- JIKA KERANJANG ADA ISINYA (SPLIT LAYOUT DI PC) -->
        <form action="checkout.php" method="POST" id="form-keranjang">
            <div class="row">
                
                <!-- KOLOM KIRI: DAFTAR BARANG -->
                <div class="col-md-8">
                    <div class="section-card">
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <input type="checkbox" class="custom-checkbox" id="check-all">
                            <label class="fw-bold" for="check-all">Pilih Semua Barang</label>
                        </div>

                        <?php 
                        foreach ($_SESSION['cart'] as $key => $qty): 
                            $pid = explode('_', $key)[0];
                            $vid = $_SESSION['cart_variant'][$key];
                            $harga = $_SESSION['cart_price'][$key];
                            $subtotal = $harga * $qty;
                            $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_produk, gambar FROM products WHERE id = '$pid'"));
                        ?>
                        <div class="product-item">
                            <input type="checkbox" name="selected_items[]" value="<?php echo $key; ?>" class="custom-checkbox item-check" data-price="<?php echo $subtotal; ?>">
                            <img src="uploads/<?php echo $p['gambar']; ?>">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-truncate" style="font-size:15px; max-width:80%;"><?php echo $p['nama_produk']; ?></div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-danger fw-bold fs-5">Rp <?php echo number_format($harga,0,',','.'); ?></div>
                                    <div class="fw-bold bg-light px-3 py-1 rounded border">x<?php echo $qty; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- KOLOM KANAN: RINGKASAN BELANJA (HANYA TAMPIL BENTUK KOTAK DI PC) -->
                <div class="col-md-4 desktop-only">
                    <div class="section-card summary-box border border-success border-opacity-25">
                        <h5 class="fw-bold mb-3">Ringkasan Belanja</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Total Harga (<span class="count_display">0</span> barang)</span>
                            <span class="fw-bold total_display">Rp 0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total Tagihan</span>
                            <span class="text-success fw-bold fs-5 total_display">Rp 0</span>
                        </div>
                        <button type="submit" class="btn-checkout btn-submit" disabled>Beli Sekarang</button>
                    </div>
                </div>

            </div>

            <!-- BOTTOM BAR: HANYA TAMPIL DI HP (MENGGANTIKAN KOTAK RINGKASAN KANAN) -->
            <div class="bottom-bar mobile-only">
                <div>
                    <span class="text-muted" style="font-size: 13px;">Total Pembayaran</span><br>
                    <span class="text-success fw-bold fs-5 total_display">Rp 0</span>
                </div>
                <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 btn-submit" disabled>Checkout (<span class="count_display">0</span>)</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    function calculateTotal() {
        let total = 0;
        let count = 0;
        $('.item-check:checked').each(function() {
            total += parseInt($(this).data('price'));
            count++;
        });
        
        $('.total_display').text('Rp ' + total.toLocaleString('id-ID'));
        $('.count_display').text(count);
        
        if(count > 0) { $('.btn-submit').prop('disabled', false); } 
        else { $('.btn-submit').prop('disabled', true); }
    }

    $('.item-check').change(function() {
        if ($('.item-check:checked').length === $('.item-check').length) {
            $('#check-all').prop('checked', true);
        } else {
            $('#check-all').prop('checked', false);
        }
        calculateTotal();
    });

    $('#check-all').change(function() {
        $('.item-check').prop('checked', $(this).prop('checked'));
        calculateTotal();
    });
});
</script>
</body>
</html>
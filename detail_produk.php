<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pembeli_id = $_SESSION['id'];

$q_produk = mysqli_query($conn, "SELECT p.*, u.username as nama_toko, u.alamat_lengkap FROM products p JOIN users u ON p.penjual_id = u.id WHERE p.id = '$id_produk'");
$produk = mysqli_fetch_assoc($q_produk);
if (!$produk) { die("Produk tidak ditemukan atau telah dihapus!"); }

$q_images = mysqli_query($conn, "SELECT gambar FROM product_images WHERE produk_id = '$id_produk'");
$q_varian = mysqli_query($conn, "SELECT * FROM product_variants WHERE produk_id = '$id_produk'");
$punya_varian = mysqli_num_rows($q_varian) > 0;

$cek_wishlist = mysqli_query($conn, "SELECT id FROM wishlists WHERE pembeli_id = '$pembeli_id' AND produk_id = '$id_produk'");
$is_wishlist = mysqli_num_rows($cek_wishlist) > 0;

$q_ulasan = mysqli_query($conn, "SELECT r.*, u.username FROM reviews r JOIN users u ON r.pembeli_id = u.id WHERE r.produk_id = '$id_produk' ORDER BY r.id DESC LIMIT 5");

$id_penjual = $produk['penjual_id'];
$q_rek = mysqli_query($conn, "SELECT id, nama_produk, harga, gambar FROM products WHERE penjual_id = '$id_penjual' AND id != '$id_produk' LIMIT 5");

// Logika Tambah Keranjang
if (isset($_POST['tambah_keranjang'])) {
    $vid = isset($_POST['varian_id']) ? $_POST['varian_id'] : 0;
    $qty = (int)$_POST['jumlah'];
    
    $stok_tersedia = $produk['stok'];
    $harga_final = $produk['harga'];
    if ($vid > 0) {
        $v_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok_varian, harga_tambahan FROM product_variants WHERE id = '$vid'"));
        $stok_tersedia = $v_data['stok_varian'];
        $harga_final += $v_data['harga_tambahan'];
    }

    if ($qty > $stok_tersedia) {
        echo "<script>alert('Gagal! Stok barang tidak mencukupi.'); window.history.back();</script>";
        exit;
    }

    $cart_key = $id_produk . '_' . $vid;
    if(isset($_SESSION['cart'][$cart_key])) { $_SESSION['cart'][$cart_key] += $qty; } 
    else {
        $_SESSION['cart'][$cart_key] = $qty;
        $_SESSION['cart_variant'][$cart_key] = $vid;
        $_SESSION['cart_price'][$cart_key] = $harga_final;
    }
    echo "<script>alert('Berhasil ditambahkan ke Keranjang!'); window.location='keranjang.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $produk['nama_produk']; ?> - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        /* NAVBAR RESPONSIVE */
        .top-nav { background: #fff; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .top-nav a, .top-nav button { color: #333; font-size: 22px; text-decoration: none; background: none; border: none; }
        
        .section-card { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #eee; }
        
        /* CAROUSEL GAMBAR PC & HP */
        .carousel-item img { width: 100%; object-fit: cover; background: #fff; border-radius: 12px; }
        
        .price-text { color: #fa591d; font-size: 28px; font-weight: 800; }
        .title-text { font-size: 20px; font-weight: 600; color: #333; line-height: 1.4; margin-top: 5px; }
        
        /* VARIAN */
        .variant-box { display: none; }
        .variant-label { border: 1px solid #ddd; padding: 8px 15px; border-radius: 8px; cursor: pointer; display: inline-block; margin-right: 10px; margin-bottom: 10px; font-size: 14px; background: #fff; color: #555; }
        .variant-box:checked + .variant-label { border-color: #118a44; background: #e8f5e9; color: #118a44; font-weight: bold; }
        
        .store-icon { width: 50px; height: 50px; background: #118a44; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; }
        
        /* BUTTONS */
        .btn-cart { background: linear-gradient(135deg, #118a44, #18b35a); color: #fff; border-radius: 12px; font-weight: bold; border: none; padding: 12px; width: 100%; transition: 0.3s; }
        .btn-cart:disabled { background: #cccccc; color: #666666; cursor: not-allowed; }
        
        /* REKOMENDASI SCROLL */
        .rek-scroll { display: flex; overflow-x: auto; gap: 12px; padding-bottom: 10px; scrollbar-width: none; }
        .rek-scroll::-webkit-scrollbar { display: none; }
        .rek-card-sm { min-width: 140px; max-width: 140px; border-radius: 12px; border: 1px solid #eee; overflow: hidden; background: #fff; text-decoration: none; display: flex; flex-direction: column; }
        .rek-card-sm img { width: 100%; height: 140px; object-fit: cover; }
        
        /* RESPONSIVE LAYOUTS */
        @media (min-width: 768px) {
            .mobile-only { display: none !important; }
            .carousel-item img { height: 500px; }
        }
        @media (max-width: 767px) {
            .desktop-only { display: none !important; }
            body { padding-bottom: 90px; }
            .carousel-item img { height: 400px; border-radius: 0; }
            /* BOTTOM BAR STICKY UNTUK HP */
            .bottom-bar { position: fixed; bottom: 0; left: 0; width: 100%; background: #fff; padding: 12px 15px; box-shadow: 0 -4px 15px rgba(0,0,0,0.1); display: flex; gap: 10px; z-index: 1000; border-radius: 20px 20px 0 0; }
            .btn-chat-mobile { border: 1px solid #118a44; color: #118a44; background: #fff; width: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <a href="marketplace_pembeli.php"><i class="bi bi-arrow-left"></i></a>
            <h5 class="m-0 fw-bold desktop-only">Detail Produk</h5>
        </div>
        <div class="d-flex gap-4">
            <button id="btn-wishlist" data-id="<?php echo $id_produk; ?>">
                <i class="bi <?php echo $is_wishlist ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>" id="icon-wishlist"></i>
            </button>
            <a href="keranjang.php"><i class="bi bi-cart3"></i></a>
        </div>
    </div>
</div>

<form method="POST">
    <div class="container mt-md-4 mb-5">
        <div class="row">
            
            <!-- KOLOM KIRI (GAMBAR) -->
            <div class="col-md-5 mb-3 mb-md-0">
                <div id="productCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active"><img src="uploads/<?php echo $produk['gambar']; ?>"></div>
                        <?php mysqli_data_seek($q_images, 0); while($img = mysqli_fetch_assoc($q_images)): ?>
                        <div class="carousel-item"><img src="uploads/<?php echo $img['gambar']; ?>"></div>
                        <?php endwhile; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <!-- KOLOM KANAN (INFO & AKSI) -->
            <div class="col-md-7">
                <div class="section-card mt-3 mt-md-0">
                    <div class="price-text" id="display_price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                    <div class="title-text"><?php echo $produk['nama_produk']; ?></div>
                    <div class="d-flex align-items-center mt-3 text-muted" style="font-size: 14px;">
                        <i class="bi bi-star-fill text-warning me-1"></i> 4.9 &bull; <span class="ms-1 border-end pe-2 me-2">Terjual 100+</span>
                        <i class="bi bi-box-seam me-1"></i> Stok: <span id="display_stok" class="fw-bold ms-1 <?php echo ($produk['stok'] == 0 && !$punya_varian) ? 'text-danger' : ''; ?>"><?php echo $produk['stok']; ?></span>
                    </div>
                </div>

                <?php if($punya_varian): ?>
                <div class="section-card">
                    <h6 class="fw-bold mb-3">Pilih Varian</h6>
                    <div>
                        <?php while($v = mysqli_fetch_assoc($q_varian)): ?>
                            <input type="radio" name="varian_id" id="var_<?php echo $v['id']; ?>" class="variant-box" value="<?php echo $v['id']; ?>" data-harga="<?php echo $v['harga_tambahan']; ?>" data-stok="<?php echo $v['stok_varian']; ?>" required>
                            <label class="variant-label" for="var_<?php echo $v['id']; ?>">
                                <?php echo $v['nama_varian']; ?> 
                                <?php if($v['harga_tambahan'] > 0) echo "(+ Rp ".number_format($v['harga_tambahan'],0,',','.').")"; ?>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- AKSI PC (Tampil di Kanan) -->
                <div class="section-card desktop-only bg-light border-success border-opacity-50">
                    <h6 class="fw-bold mb-3">Atur Pembelian</h6>
                    <input type="hidden" name="jumlah" value="1">
                    <div class="d-flex gap-2">
                        <a href="chat.php?tujuan=<?php echo $produk['penjual_id']; ?>" class="btn btn-outline-success fw-bold px-4 rounded-pill"><i class="bi bi-chat-dots me-2"></i>Chat Penjual</a>
                        <?php if(!$punya_varian && $produk['stok'] == 0): ?>
                            <button type="button" class="btn-cart flex-grow-1" disabled id="btn_submit_cart_pc">Stok Habis</button>
                        <?php else: ?>
                            <button type="submit" name="tambah_keranjang" class="btn-cart flex-grow-1" id="btn_submit_cart_pc"><i class="bi bi-cart-plus me-2"></i>Masukkan Keranjang</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section-card d-flex align-items-center">
                    <div class="store-icon me-3"><?php echo strtoupper(substr($produk['nama_toko'], 0, 1)); ?></div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0"><?php echo $produk['nama_toko']; ?></h6>
                        <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo $produk['alamat_lengkap']; ?></small>
                    </div>
                </div>

                <div class="section-card">
                    <h6 class="fw-bold mb-3">Deskripsi Produk</h6>
                    <p class="text-muted m-0" style="font-size: 14px; white-space: pre-wrap;"><?php echo $produk['deskripsi']; ?></p>
                </div>

            </div> <!-- Akhir Kolom Kanan -->
        </div> <!-- Akhir Row Utama -->

        <!-- BARIS REKOMENDASI & ULASAN -->
        <div class="row mt-3">
            <div class="col-md-7 offset-md-5">
                
                <?php if(mysqli_num_rows($q_rek) > 0): ?>
                <div class="section-card bg-transparent border-0 px-0">
                    <h6 class="fw-bold mb-3">Lainnya dari toko ini</h6>
                    <div class="rek-scroll">
                        <?php while($rek = mysqli_fetch_assoc($q_rek)): ?>
                        <a href="detail_produk.php?id=<?php echo $rek['id']; ?>" class="rek-card-sm text-dark">
                            <img src="uploads/<?php echo $rek['gambar']; ?>">
                            <div class="p-2 d-flex flex-column flex-grow-1">
                                <div style="font-size: 12px; line-height: 1.2; height: 28px; overflow: hidden;"><?php echo $rek['nama_produk']; ?></div>
                                <div class="text-danger fw-bold mt-auto pt-1" style="font-size: 13px;">Rp <?php echo number_format($rek['harga'],0,',','.'); ?></div>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="section-card">
                    <h6 class="fw-bold mb-4">Ulasan Pembeli</h6>
                    <?php if(mysqli_num_rows($q_ulasan) > 0): ?>
                        <?php while($ul = mysqli_fetch_assoc($q_ulasan)): ?>
                        <div class="mb-3 border-bottom pb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold small"><?php echo $ul['username']; ?></span>
                                <div class="text-warning small">
                                    <!-- PENAMBAHAN '?? 5' AGAR TIDAK ERROR JIKA BINTANG KOSONG -->
                                    <?php for($i=1; $i<=5; $i++){ echo ($i <= ($ul['bintang'] ?? 5)) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>'; } ?>
                                </div>
                            </div>
                            <p class="text-muted m-0 small"><?php echo $ul['komentar']; ?></p>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted small">Belum ada ulasan.</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- AKSI MOBILE (Tampil di Bawah, Floating) -->
    <div class="bottom-bar mobile-only">
        <a href="chat.php?tujuan=<?php echo $produk['penjual_id']; ?>" class="btn-chat-mobile"><i class="bi bi-chat-dots"></i></a>
        <?php if(!$punya_varian && $produk['stok'] == 0): ?>
            <button type="button" class="btn-cart shadow-sm" disabled id="btn_submit_cart_hp">Stok Habis</button>
        <?php else: ?>
            <button type="submit" name="tambah_keranjang" class="btn-cart shadow-sm" id="btn_submit_cart_hp">Masukkan Keranjang</button>
        <?php endif; ?>
    </div>
</form>

<script>
$(document).ready(function() {
    var hargaDasar = <?php echo $produk['harga']; ?>;
    
    $('.variant-box').change(function() {
        var hargaFinal = hargaDasar + parseInt($(this).data('harga'));
        var stokVarian = parseInt($(this).data('stok'));
        
        $('#display_price').html('Rp ' + hargaFinal.toLocaleString('id-ID'));
        $('#display_stok').html(stokVarian);
        
        if (stokVarian <= 0) {
            $('#display_stok').addClass('text-danger');
            $('#btn_submit_cart_pc, #btn_submit_cart_hp').prop('disabled', true).text('Stok Habis');
        } else {
            $('#display_stok').removeClass('text-danger');
            $('#btn_submit_cart_pc, #btn_submit_cart_hp').prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>Masukkan Keranjang');
            $('#btn_submit_cart_hp').text('Masukkan Keranjang');
        }
    });

    $('#btn-wishlist').click(function(e) {
        e.preventDefault();
        var pid = $(this).data('id');
        $.post('proses_wishlist.php', {produk_id: pid}, function(res) {
            if(res.trim() == 'added') $('#icon-wishlist').removeClass('bi-heart').addClass('bi-heart-fill text-danger');
            else if(res.trim() == 'removed') $('#icon-wishlist').removeClass('bi-heart-fill text-danger').addClass('bi-heart');
        });
    });
});
</script>
</body>
</html>
<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

if (!isset($_POST['selected_items']) || empty($_POST['selected_items'])) {
    echo "<script>alert('Pilih minimal satu barang untuk di-checkout!'); window.location='keranjang.php';</script>";
    exit;
}

$pembeli_id = $_SESSION['id'];
$selected_items = $_POST['selected_items'];
$total_berat = 0; 
$total_produk = 0;

// Ambil Saldo & Koin
$q_saldo = mysqli_query($conn, "SELECT saldo, sella_coins FROM users WHERE id = '$pembeli_id'");
$d_user = mysqli_fetch_assoc($q_saldo);
$saldo_saya = $d_user['saldo'];
$koin_saya = $d_user['sella_coins'] ?? 0;

// Ambil info penjual
$first_key = $selected_items[0];
$first_pid = explode('_', $first_key)[0];
$q_seller = mysqli_query($conn, "SELECT u.kurir_aktif, u.username FROM products p JOIN users u ON p.penjual_id = u.id WHERE p.id = '$first_pid'");
$d_seller = mysqli_fetch_assoc($q_seller);
$kurir_tersedia = explode(',', $d_seller['kurir_aktif']);
$nama_toko = $d_seller['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Checkout - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        /* NAVIGASI ATAS */
        .top-nav { background: #fff; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .top-nav a { color: #333; font-size: 24px; text-decoration: none; margin-right: 15px; }
        
        /* KARTU DAN ITEM */
        .section-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px; }
        .product-item { display: flex; align-items: center; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px dashed #eee; }
        .product-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px; }
        
        /* RADIO BUTTON PEMBAYARAN */
        .pay-option { border: 1px solid #ddd; border-radius: 10px; padding: 12px; margin-bottom: 10px; cursor: pointer; display: flex; align-items: center; transition: 0.2s; }
        .pay-option:hover { background-color: #f8f9fa; border-color: #118a44; }
        .pay-radio { accent-color: #fa591d; width: 18px; height: 18px; margin-right: 12px; }
        
        /* KOTAK RINGKASAN PC */
        .summary-box { position: sticky; top: 90px; border: 1px solid #fa591d; }
        .btn-pay { background: linear-gradient(135deg, #fa591d, #ff7b47); color: #fff; width: 100%; border-radius: 12px; font-weight: bold; border: none; font-size: 16px; padding: 14px; transition: 0.3s; }
        .btn-pay:hover { opacity: 0.9; transform: translateY(-2px); }

        /* BOTTOM BAR MOBILE */
        .bottom-bar { position: fixed; bottom: 0; width: 100%; background: #fff; padding: 15px; box-shadow: 0 -4px 15px rgba(0,0,0,0.1); z-index: 1000; display: flex; align-items: center; justify-content: space-between; left: 0; }
        
        /* RESPONSIVE BREAKPOINTS */
        @media (min-width: 768px) {
            .mobile-only { display: none !important; }
        }
        @media (max-width: 767px) {
            .desktop-only { display: none !important; }
            body { padding-bottom: 110px; } 
        }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="container d-flex align-items-center">
        <a href="keranjang.php"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Checkout</h5>
    </div>
</div>

<div class="container mt-4">
    <form action="proses_checkout.php" method="POST">
        
        <!-- INPUT TERSEMBUNYI UNTUK DIPROSES DI BACKEND -->
        <?php foreach($selected_items as $item_key): ?>
            <input type="hidden" name="checkout_items[]" value="<?php echo $item_key; ?>">
        <?php endforeach; ?>
        
        <input type="hidden" name="total_akhir" id="in_total" value="<?php echo $total_produk; ?>">
        <input type="hidden" name="ongkir" id="in_ongkir" value="0">
        <input type="hidden" name="diskon" id="in_diskon" value="0">
        <input type="hidden" name="koin_dipakai" id="in_koin" value="0">

        <div class="row">
            <!-- ================= KOLOM KIRI (KONTEN UTAMA) ================= -->
            <div class="col-md-8">
                
                <!-- 1. ALAMAT PENGIRIMAN -->
                <div class="section-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Alamat Pengiriman</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <select class="form-select bg-light border-0" id="prov" required> 
                                <?php include 'api_provinsi.php'; ?> 
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select bg-light border-0" id="kota" required> 
                                <option value="">Pilih Kota/Kabupaten</option> 
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <textarea name="alamat_lengkap" class="form-control bg-light border-0" rows="2" placeholder="Detail jalan, no rumah, RT/RW, patokan..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. RINGKASAN BARANG & KURIR -->
                <div class="section-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-shop text-success me-2"></i><?php echo $nama_toko; ?></h6>
                    <?php 
                    foreach ($selected_items as $key): 
                        $qty = $_SESSION['cart'][$key];
                        $pid = explode('_', $key)[0];
                        $vid = $_SESSION['cart_variant'][$key];
                        $harga = $_SESSION['cart_price'][$key];
                        
                        $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_produk, gambar, berat FROM products WHERE id = '$pid'"));
                        $total_produk += ($harga * $qty);
                        $total_berat += ($p['berat'] * $qty);
                    ?>
                    <div class="product-item">
                        <img src="uploads/<?php echo $p['gambar']; ?>">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-truncate" style="font-size:14px; max-width:90%;"><?php echo $p['nama_produk']; ?></div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="text-muted small">Rp <?php echo number_format($harga,0,',','.'); ?> x <?php echo $qty; ?></span>
                                <span class="fw-bold text-dark">Rp <?php echo number_format($harga * $qty,0,',','.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-4 pt-3 border-top">
                        <label class="fw-bold mb-2 small text-muted">Pilih Ekspedisi Pengiriman</label>
                        <select class="form-select border-success fw-bold" id="kurir" required>
                            <option value="">-- Pilih Kurir --</option>
                            <?php if(in_array('jne', $kurir_tersedia)): ?><option value="jne">JNE Reguler</option><?php endif; ?>
                            <?php if(in_array('pos', $kurir_tersedia)): ?><option value="pos">POS Indonesia</option><?php endif; ?>
                            <?php if(in_array('tiki', $kurir_tersedia)): ?><option value="tiki">TIKI ONS</option><?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- 3. METODE PEMBAYARAN -->
                <div class="section-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-wallet2 text-primary me-2"></i>Metode Pembayaran</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="pay-option">
                                <input type="radio" name="metode_pembayaran" value="sella_pay" class="pay-radio" required>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-success">SELLA Pay</div>
                                    <div class="small text-muted">Saldo: Rp <?php echo number_format($saldo_saya,0,',','.'); ?></div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="pay-option">
                                <input type="radio" name="metode_pembayaran" value="cod" class="pay-radio">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">Bayar di Tempat (COD)</div>
                                    <div class="small text-muted">Tunai ke kurir</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-3 mb-2 small text-muted">Transfer Virtual Account</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="pay-option">
                                <input type="radio" name="metode_pembayaran" value="va_bca" class="pay-radio">
                                <span class="fw-bold text-primary">BCA Virtual Account</span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="pay-option">
                                <input type="radio" name="metode_pembayaran" value="va_mandiri" class="pay-radio">
                                <span class="fw-bold text-warning">Mandiri Virtual Account</span>
                            </label>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-3 mb-2 small text-muted">E-Wallet</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="pay-option">
                                <input type="radio" name="metode_pembayaran" value="ewallet_gopay" class="pay-radio">
                                <span class="fw-bold" style="color: #00a5cf;">GoPay</span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="pay-option">
                                <input type="radio" name="metode_pembayaran" value="ewallet_dana" class="pay-radio">
                                <span class="fw-bold text-primary">DANA</span>
                            </label>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- ================= KOLOM KANAN (RINGKASAN PC) ================= -->
            <div class="col-md-4 desktop-only">
                
                <!-- KOTAK PROMO (PC) -->
                <div class="section-card bg-light border">
                    <h6 class="fw-bold mb-3"><i class="bi bi-tags-fill text-warning me-2"></i>Makin Hemat</h6>
                    <div class="input-group mb-2">
                        <input type="text" id="kode_v_pc" class="form-control bg-white" placeholder="Kode Voucher">
                        <button type="button" class="btn btn-dark fw-bold btn_v" data-target="#kode_v_pc">Pakai</button>
                    </div>
                    <div class="v_msg small mb-3"></div>
                    
                    <?php if($koin_saya > 0): ?>
                    <div class="form-check form-switch border bg-white p-2 rounded d-flex justify-content-between align-items-center">
                        <label class="form-check-label fw-bold text-warning mb-0" for="pakai_koin_pc" style="font-size:14px;">
                            <i class="bi bi-coin"></i> Tukar <?php echo number_format($koin_saya,0,',','.'); ?> Koin
                        </label>
                        <input class="form-check-input m-0 pakai_koin" type="checkbox" id="pakai_koin_pc" value="<?php echo $koin_saya; ?>">
                    </div>
                    <?php endif; ?>
                </div>

                <!-- KOTAK TAGIHAN (PC) -->
                <div class="section-card summary-box shadow-sm">
                    <h5 class="fw-bold mb-4">Ringkasan Belanja</h5>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Total Produk</span>
                        <span>Rp <?php echo number_format($total_produk,0,',','.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Total Ongkos Kirim</span>
                        <span class="display_ongkir">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-danger fw-bold row_diskon" style="display:none !important;">
                        <span>Diskon Voucher</span>
                        <span class="display_diskon">- Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-warning fw-bold row_koin" style="display:none !important;">
                        <span>Potongan Koin</span>
                        <span class="display_koin">- Rp 0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total Tagihan</span>
                        <span class="text-danger fw-bold fs-4 display_total">Rp <?php echo number_format($total_produk,0,',','.'); ?></span>
                    </div>
                    <button type="submit" class="btn-pay shadow-sm"><i class="bi bi-bag-check-fill me-2"></i>Buat Pesanan</button>
                </div>
            </div>

        </div> <!-- Akhir Row -->

        <!-- ================= BOTTOM BAR (RINGKASAN MOBILE) ================= -->
        <div class="mobile-only">
            
            <!-- KOTAK PROMO (MOBILE) -->
            <div class="section-card">
                <h6 class="fw-bold mb-3"><i class="bi bi-tags-fill text-warning me-2"></i>Makin Hemat</h6>
                <div class="input-group mb-2">
                    <input type="text" id="kode_v_hp" class="form-control" placeholder="Kode Voucher">
                    <button type="button" class="btn btn-dark fw-bold btn_v" data-target="#kode_v_hp">Pakai</button>
                </div>
                <div class="v_msg small mb-3"></div>
                
                <?php if($koin_saya > 0): ?>
                <div class="form-check form-switch border p-2 rounded d-flex justify-content-between align-items-center">
                    <label class="form-check-label fw-bold text-warning mb-0" for="pakai_koin_hp" style="font-size:14px;">
                        <i class="bi bi-coin"></i> Tukar <?php echo number_format($koin_saya,0,',','.'); ?> Koin
                    </label>
                    <input class="form-check-input m-0 pakai_koin" type="checkbox" id="pakai_koin_hp" value="<?php echo $koin_saya; ?>">
                </div>
                <?php endif; ?>
            </div>

            <!-- RINCIAN (MOBILE) -->
            <div class="section-card mb-4">
                <h6 class="fw-bold mb-3">Rincian Pembayaran</h6>
                <div class="d-flex justify-content-between mb-1 small text-muted"><span>Subtotal Produk</span> <span>Rp <?php echo number_format($total_produk,0,',','.'); ?></span></div>
                <div class="d-flex justify-content-between mb-1 small text-muted"><span>Ongkos Kirim</span> <span class="display_ongkir">Rp 0</span></div>
                <div class="d-flex justify-content-between mb-1 text-danger fw-bold small row_diskon" style="display:none !important;"><span>Diskon</span> <span class="display_diskon">- Rp 0</span></div>
                <div class="d-flex justify-content-between mb-1 text-warning fw-bold small row_koin" style="display:none !important;"><span>Koin</span> <span class="display_koin">- Rp 0</span></div>
            </div>

            <!-- BAR BAWAH STICKY (MOBILE) -->
            <div class="bottom-bar">
                <div>
                    <span class="text-muted" style="font-size:13px;">Total Bayar</span><br>
                    <span class="text-danger fw-bold fs-4 display_total">Rp <?php echo number_format($total_produk,0,',','.'); ?></span>
                </div>
                <button type="submit" class="btn-pay shadow-sm" style="width: auto; padding: 10px 25px;">Buat Pesanan</button>
            </div>
        </div>

    </form>
</div>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<!-- Menggunakan class (.display_ongkir) agar bisa mengupdate tampilan di PC dan Mobile sekaligus -->
<script>
$(document).ready(function(){
    // Set in_total awal saat halaman dimuat
    $('#in_total').val(<?php echo $total_produk; ?>);

    $('#prov').change(function(){
        $.post('api_kota.php', {prov_id: $(this).val()}, function(data){ $('#kota').html(data); });
    });
    
    function hitungTotal() {
        var p = <?php echo $total_produk; ?>;
        var o = parseInt($('#in_ongkir').val()) || 0;
        var d = parseInt($('#in_diskon').val()) || 0;
        var k = parseInt($('#in_koin').val()) || 0;
        var total = p + o - d - k;
        
        // Update tampilan di semua tempat (PC & HP)
        $('.display_total').html("Rp " + total.toLocaleString('id-ID'));
        $('#in_total').val(total);
    }
    
    $('#kota, #kurir').change(function(){
        var t = $('#kota').val(), k = $('#kurir').val(), b = <?php echo $total_berat; ?>;
        if(t && k) {
            $('.display_ongkir').html("Menghitung...");
            $.post('api_ongkir.php', {tujuan:t, kurir:k, berat:b}, function(data){
                var o = parseInt(data) || 0; 
                if(o > 0) {
                    $('.display_ongkir').html("Rp " + o.toLocaleString('id-ID'));
                    $('#in_ongkir').val(o);
                    
                    // Trigger ulang perhitungan koin jika sedang dicentang
                    if ($('.pakai_koin').is(':checked')) { $('.pakai_koin').trigger('change'); } 
                    else { hitungTotal(); }
                } else { $('.display_ongkir').html("Gagal cek API"); }
            });
        }
    });
    
    $('.btn_v').click(function(e){
        e.preventDefault();
        var targetInput = $(this).data('target');
        var k = $(targetInput).val();
        var t = <?php echo $total_produk; ?>;
        
        $.post('api_cek_voucher.php', {kode:k, total_harga:t}, function(data){
            var res = JSON.parse(data);
            if(res.status == 'sukses'){
                var d = res.potongan;
                $('.row_diskon').attr('style','display:flex !important');
                $('.display_diskon').html("- Rp " + d.toLocaleString('id-ID'));
                $('#in_diskon').val(d);
                $('.v_msg').html("<span class='text-success'>Berhasil! Diskon diterapkan.</span>");
                
                if ($('.pakai_koin').is(':checked')) { $('.pakai_koin').trigger('change'); } 
                else { hitungTotal(); }
            } else { $('.v_msg').html("<span class='text-danger'>"+res.pesan+"</span>"); }
        });
    });
    
    // Sync checkbox koin antara PC dan Mobile
    $('.pakai_koin').change(function(){
        var isChecked = $(this).is(':checked');
        $('.pakai_koin').prop('checked', isChecked); // Samakan status centang
        
        var p = <?php echo $total_produk; ?>;
        var o = parseInt($('#in_ongkir').val()) || 0;
        var d = parseInt($('#in_diskon').val()) || 0;
        var ts = p + o - d; 
        var koin = 0;
        
        if (isChecked) {
            koin = parseInt($(this).val());
            if(koin > ts) koin = ts; // Koin tidak boleh lebih dari total tagihan
            $('.row_koin').attr('style','display:flex !important');
            $('.display_koin').html("- Rp " + koin.toLocaleString('id-ID'));
        } else { 
            $('.row_koin').attr('style','display:none !important'); 
        }
        
        $('#in_koin').val(koin);
        hitungTotal();
    });
});
</script>
</body>
</html>
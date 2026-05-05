<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pembeli_id = $_SESSION['id'];
    
    // Tangkap data dari form checkout
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    $items = $_POST['checkout_items']; // Ini array barang yang dicentang
    
    $subtotal = (int)$_POST['total_akhir'];
    $ongkir = (int)$_POST['ongkir'];
    $diskon = (int)$_POST['diskon'];
    $koin_dipakai = (int)$_POST['koin_dipakai'];
    
    // Hitung ulang total untuk keamanan
    $total_bayar = $subtotal + $ongkir - $diskon - $koin_dipakai;

    // 1. Cek Saldo jika metode bayarnya SELLA Pay
    if ($metode == 'sella_pay') {
        $user_q = mysqli_query($conn, "SELECT saldo FROM users WHERE id = '$pembeli_id'");
        $user_d = mysqli_fetch_assoc($user_q);
        if ($user_d['saldo'] < $total_bayar) {
            echo "<script>alert('Saldo SELLA Pay Anda tidak mencukupi! Silakan pilih metode lain.'); window.history.back();</script>";
            exit;
        }
        // Potong saldo
        mysqli_query($conn, "UPDATE users SET saldo = saldo - $total_bayar WHERE id = '$pembeli_id'");
    }

    // 2. Potong Koin jika pembeli menukar koinnya
    if ($koin_dipakai > 0) {
        mysqli_query($conn, "UPDATE users SET sella_coins = sella_coins - $koin_dipakai WHERE id = '$pembeli_id'");
    }

    // 3. Simpan data utama ke tabel orders
    // Catatan: Untuk Transfer VA & E-Wallet, di simulasi ini kita anggap pembeli langsung bayar (status: dikemas)
    $query_order = "INSERT INTO orders (pembeli_id, total_bayar, metode_pembayaran, status_pesanan) 
                    VALUES ('$pembeli_id', '$total_bayar', '$metode', 'dikemas')";
    mysqli_query($conn, $query_order);
    $order_id = mysqli_insert_id($conn); // Ambil ID pesanan yang baru saja terbuat

    // 4. Update alamat user (agar next order tidak perlu ngetik ulang)
    mysqli_query($conn, "UPDATE users SET alamat_lengkap = '$alamat' WHERE id = '$pembeli_id'");

    // 5. Masukkan rincian barang, kurangi stok, dan hapus dari keranjang
    foreach ($items as $key) {
        $pid = explode('_', $key)[0];
        $vid = $_SESSION['cart_variant'][$key];
        $qty = $_SESSION['cart'][$key];
        $harga = $_SESSION['cart_price'][$key];
        $subtotal_item = $harga * $qty;

        // Insert ke order_details
        mysqli_query($conn, "INSERT INTO order_details (order_id, produk_id, jumlah, subtotal) 
                        VALUES ('$order_id', '$pid', '$qty', '$subtotal_item')");

        // Kurangi stok produk utama
        mysqli_query($conn, "UPDATE products SET stok = stok - $qty WHERE id = '$pid'");
        
        // Kurangi stok varian jika ada
        if ($vid > 0) {
            mysqli_query($conn, "UPDATE product_variants SET stok_varian = stok_varian - $qty WHERE id = '$vid'");
        }

        // HAPUS HANYA BARANG INI DARI KERANJANG
        unset($_SESSION['cart'][$key]);
        unset($_SESSION['cart_variant'][$key]);
        unset($_SESSION['cart_price'][$key]);
    }

    // 6. Berikan Cashback Koin (Contoh: 1% dari total bayar)
    $cashback = floor($total_bayar * 0.01);
    if ($cashback > 0) {
        mysqli_query($conn, "UPDATE users SET sella_coins = sella_coins + $cashback WHERE id = '$pembeli_id'");
    }

    // Alihkan ke halaman pelacakan pesanan
    echo "<script>
            alert('Pesanan Berhasil Dibuat! Anda mendapatkan cashback $cashback SELLA Coins.'); 
            window.location='pesanan_saya.php';
          </script>";
}
?>
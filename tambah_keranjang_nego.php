<?php
session_start();
include 'koneksi.php';

// Pastikan parameter produk_id dan harga tersedia di URL
if (isset($_GET['produk_id']) && isset($_GET['harga'])) {
    $id_produk = mysqli_real_escape_string($conn, $_GET['produk_id']);
    $harga_nego = mysqli_real_escape_string($conn, $_GET['harga']);

    // 1. Inisialisasi session keranjang jika belum ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // 2. Inisialisasi session khusus untuk menyimpan harga hasil nego
    if (!isset($_SESSION['cart_nego'])) {
        $_SESSION['cart_nego'] = [];
    }
    
    // 3. Simpan harga kesepakatan ke dalam session nego
    // Ini akan menimpa harga normal saat di halaman keranjang.php
    $_SESSION['cart_nego'][$id_produk] = $harga_nego;
    
    // 4. Tambahkan jumlah barang ke keranjang (default: 1)
    if (isset($_SESSION['cart'][$id_produk])) {
        // Jika barang sudah ada di keranjang, tambah jumlahnya
        $_SESSION['cart'][$id_produk] += 1;
    } else {
        // Jika belum ada, set jumlah jadi 1
        $_SESSION['cart'][$id_produk] = 1;
    }

    // 5. Berikan notifikasi dan arahkan pembeli ke halaman keranjang
    echo "<script>
            alert('Berhasil! Produk ditambahkan ke keranjang dengan harga nego: Rp " . number_format($harga_nego, 0, ',', '.') . "');
            window.location='keranjang.php';
          </script>";
    exit;

} else {
    // Jika diakses tanpa parameter yang benar, kembalikan ke marketplace
    header("Location: marketplace_pembeli.php");
    exit;
}
?>
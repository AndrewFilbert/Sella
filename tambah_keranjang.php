<?php
session_start();
include 'koneksi.php';

if (isset($_POST['produk_id'])) {
    $id = $_POST['produk_id'];
    $jumlah = $_POST['jumlah'];
    $harga = $_POST['harga_final'];
    $variant_id = $_POST['variant_id'] ?? 0;

    // Kunci unik: gabungan ID produk dan ID varian
    $key = $id . "_" . $variant_id;

    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
    if (!isset($_SESSION['cart_variant'])) { $_SESSION['cart_variant'] = []; }
    if (!isset($_SESSION['cart_price'])) { $_SESSION['cart_price'] = []; }

    // Simpan data ke session
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key] += $jumlah;
    } else {
        $_SESSION['cart'][$key] = $jumlah;
    }

    // Simpan info tambahan agar keranjang tahu ini produk apa dan varian apa
    $_SESSION['cart_variant'][$key] = $variant_id;
    $_SESSION['cart_price'][$key] = $harga;

    echo "<script>alert('Berhasil ditambah ke keranjang!'); window.location='marketplace_pembeli.php';</script>";
}
?>
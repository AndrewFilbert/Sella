<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    echo "error"; exit;
}

$pembeli_id = $_SESSION['id'];
$produk_id = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;

// Cek apakah produk sudah ada di wishlist
$cek = mysqli_query($conn, "SELECT id FROM wishlists WHERE pembeli_id = '$pembeli_id' AND produk_id = '$produk_id'");

if (mysqli_num_rows($cek) > 0) {
    // Jika sudah ada, Hapus dari favorit
    mysqli_query($conn, "DELETE FROM wishlists WHERE pembeli_id = '$pembeli_id' AND produk_id = '$produk_id'");
    echo "removed";
} else {
    // Jika belum ada, Tambahkan ke favorit
    mysqli_query($conn, "INSERT INTO wishlists (pembeli_id, produk_id) VALUES ('$pembeli_id', '$produk_id')");
    echo "added";
}
?>
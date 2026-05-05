<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual' || !isset($_GET['id'])) {
    header("Location: dashboard_penjual.php");
    exit;
}

$id_produk = $_GET['id'];
$penjual_id = $_SESSION['id'];

// Ambil nama file gambar
$query = "SELECT gambar FROM products WHERE id = '$id_produk' AND penjual_id = '$penjual_id'";
$result = mysqli_query($conn, $query);
$produk = mysqli_fetch_assoc($result);

if ($produk) {
    // Hapus file fisik
    $gambar_lama = "uploads/" . $produk['gambar'];
    if (file_exists($gambar_lama)) {
        unlink($gambar_lama); 
    }

    // Hapus data di DB
    mysqli_query($conn, "DELETE FROM products WHERE id = '$id_produk' AND penjual_id = '$penjual_id'");
    
    echo "<script>alert('Produk berhasil dihapus!'); window.location='dashboard_penjual.php';</script>";
} else {
    echo "<script>alert('Akses ditolak!'); window.location='dashboard_penjual.php';</script>";
}
?>
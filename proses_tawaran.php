<?php
session_start();
include 'koneksi.php';

// Proteksi keamanan: Hanya penjual yang berhak merespons tawaran
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    exit("Akses Ilegal! Anda bukan penjual.");
}

// Pastikan semua parameter URL yang dibutuhkan tersedia
if (!isset($_GET['id_pesan']) || !isset($_GET['aksi']) || !isset($_GET['lawan_id'])) {
    exit("Data tidak lengkap!");
}

// Mengamankan data dari URL
$id_pesan = mysqli_real_escape_string($conn, $_GET['id_pesan']);
$aksi = mysqli_real_escape_string($conn, $_GET['aksi']); // Isinya pasti 'diterima' atau 'ditolak'
$lawan_id = mysqli_real_escape_string($conn, $_GET['lawan_id']);
$saya_id = $_SESSION['id'];

// Validasi aksi untuk memastikan tidak ada manipulasi teks dari URL
if ($aksi === 'diterima' || $aksi === 'ditolak') {
    
    // Update status tawaran di database
    // Kita pastikan penerima_id adalah $saya_id agar penjual lain tidak bisa iseng memanipulasi ID pesan
    $query = "UPDATE messages 
              SET status_tawaran = '$aksi' 
              WHERE id = '$id_pesan' AND penerima_id = '$saya_id'";
              
    if (mysqli_query($conn, $query)) {
        // Jika berhasil, tendang kembali ke halaman chat dengan pembeli tersebut
        header("Location: chat.php?lawan_id=$lawan_id");
        exit;
    } else {
        echo "Gagal memproses tawaran: " . mysqli_error($conn);
    }
    
} else {
    exit("Aksi yang diminta tidak dikenali oleh sistem.");
}
?>
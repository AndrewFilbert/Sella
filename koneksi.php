<?php
// 1. MENGAKTIFKAN RADAR ERROR (Pendeteksi Error 500)
// Dua baris ini akan memaksa server menampilkan pesan error asli di layar
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. KREDENSIAL DATABASE (WAJIB DIGANTI!)
// Silakan ganti tulisan di dalam tanda kutip dengan data dari InfinityFree Anda
$host = "sql308.infinityfree.com"; // Ganti dengan MySQL Host Name (Contoh: sql105.infinityfree.com)
$user = "if0_41839028";             // Ganti dengan MySQL User Name (Contoh: if0_41839028)
$pass = "bEcFyJvykrv7xTF";      // Ganti dengan vPanel / Akun Password Anda
$db   = "if0_41839028_sella_db";    // Ganti dengan MySQL Database Name yang Anda buat di cPanel

// 3. MEMBUAT KONEKSI
$conn = mysqli_connect($host, $user, $pass, $db);

// 4. CEK KONEKSI
if (!$conn) {
    // Jika koneksi gagal, sistem akan berhenti dan menampilkan alasan gagalnya
    die("JAAAH GAGAL KONEK BOSS! Alasannya: " . mysqli_connect_error());
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'koneksi.php';

echo "<div style='font-family: Courier New; background: #0f0f0f; color: #d4af37; padding: 50px; text-align: center; height: 100vh;'>";
echo "<h2>🪄 Inisialisasi Sistem Admin Mewah...</h2><br>";

// 1. UPDATE DATABASE OTOMATIS: Tambahkan kolom biaya_admin di tabel orders
$cek_kolom = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'biaya_admin'");
if(mysqli_num_rows($cek_kolom) == 0){
    $tambah_kolom = mysqli_query($conn, "ALTER TABLE orders ADD biaya_admin INT DEFAULT 0 AFTER total_bayar");
    if($tambah_kolom) echo "✅ Kolom Biaya Admin berhasil ditambahkan ke database.<br>";
    else echo "❌ Gagal menambah kolom: " . mysqli_error($conn) . "<br>";
} else {
    echo "✅ Kolom Biaya Admin sudah siap.<br>";
}

// 2. BUAT AKUN ADMIN EKSKLUSIF
$username = 'boss_sella';
$password_mentah = 'sultan123'; // Ini password yang akan Anda gunakan
$password_hash = password_hash($password_mentah, PASSWORD_DEFAULT);

$cek_admin = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
if(mysqli_num_rows($cek_admin) == 0){
    $buat_admin = mysqli_query($conn, "INSERT INTO users (username, password, role, no_hp, alamat_lengkap, saldo, sella_coins) VALUES ('$username', '$password_hash', 'admin', '08000000000', 'Istana SELLA', 0, 0)");
    
    if($buat_admin) {
        echo "<h3 style='color: #28a745; margin-top: 30px;'>🎉 Akun Admin Berhasil Diciptakan!</h3>";
        echo "<div style='border: 1px dashed #d4af37; display: inline-block; padding: 20px; margin-top: 10px;'>";
        echo "Username: <b>boss_sella</b><br>";
        echo "Password: <b>sultan123</b><br>";
        echo "</div>";
    }
} else {
    echo "<h3 style='color: #17a2b8; margin-top: 30px;'>⚡ Akun Admin sudah tersedia.</h3>";
    echo "Gunakan Username: <b>boss_sella</b> | Password: <b>sultan123</b>";
}

echo "<br><br><a href='login.php' style='background: #d4af37; color: #000; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 5px;'>Menuju Halaman Login</a>";
echo "</div>";
?>
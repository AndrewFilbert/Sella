<?php
include 'koneksi.php';

$username = "AdminSELLA";
$email = "admin@sella.com";
$password_mentah = "SellaAdmin123"; // Ini password untuk login admin nanti
$password_enkripsi = password_hash($password_mentah, PASSWORD_DEFAULT);
$role = "admin";

$query = "INSERT INTO users (username, password, email, role) VALUES ('$username', '$password_enkripsi', '$email', '$role')";

if (mysqli_query($conn, $query)) {
    echo "<h1>Sukses!</h1><p>Akun Admin berhasil dibuat. Silakan hapus file buat_admin.php ini dari Visual Studio Code Anda demi keamanan.</p>";
    echo "<a href='login.php'>Pergi ke halaman Login</a>";
} else {
    echo "Gagal membuat admin: " . mysqli_error($conn);
}
?>
<?php
session_start();
include 'koneksi.php';

$order_id = $_GET['order_id'];
$pembeli_id = $_SESSION['id'];

// 1. Ambil total bayar dari pesanan ini untuk dihitung cashback-nya
$q_order = mysqli_query($conn, "SELECT total_bayar, status_pesanan FROM orders WHERE id = '$order_id' AND pembeli_id = '$pembeli_id'");
$d_order = mysqli_fetch_assoc($q_order);

// Cegah eksploitasi (refresh halaman berkali-kali untuk panen koin)
if ($d_order['status_pesanan'] == 'selesai') {
    echo "<script>alert('Pesanan ini sudah selesai sebelumnya!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// 2. Ubah status pesanan menjadi selesai
mysqli_query($conn, "UPDATE orders SET status_pesanan = 'selesai' WHERE id = '$order_id'");

// 3. Teruskan dana ke saldo penjual
$q_detail = mysqli_query($conn, "SELECT order_details.subtotal, products.penjual_id FROM order_details JOIN products ON order_details.produk_id = products.id WHERE order_details.order_id = '$order_id'");

while ($row = mysqli_fetch_assoc($q_detail)) {
    $subtotal = $row['subtotal'];
    $penjual_id = $row['penjual_id'];
    mysqli_query($conn, "UPDATE users SET saldo = saldo + $subtotal WHERE id = '$penjual_id'");
}

// 4. BERIKAN CASHBACK KOIN KE PEMBELI (Contoh: 1% dari total belanja)
$cashback_koin = floor($d_order['total_bayar'] * 0.01); 
mysqli_query($conn, "UPDATE users SET sella_coins = sella_coins + $cashback_koin WHERE id = '$pembeli_id'");

echo "<script>alert('Pesanan selesai! Anda mendapatkan $cashback_koin SELLA Coins.'); window.location='pesanan_saya.php';</script>";
?>
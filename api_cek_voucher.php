<?php
$kode = isset($_POST['kode']) ? strtoupper(trim($_POST['kode'])) : '';
$total_harga = isset($_POST['total_harga']) ? (int)$_POST['total_harga'] : 0;

// Daftar Voucher Dummy
if ($kode == 'SELLA50') {
    // Potongan tetap Rp 50.000
    echo json_encode(['status' => 'sukses', 'potongan' => 50000]);
} elseif ($kode == 'DISKON10') {
    // Diskon 10% dari total belanja
    $potongan = $total_harga * 0.1;
    echo json_encode(['status' => 'sukses', 'potongan' => $potongan]);
} else {
    // Jika kode salah
    echo json_encode(['status' => 'gagal', 'pesan' => 'Kode voucher tidak valid atau sudah kedaluwarsa.']);
}
?>
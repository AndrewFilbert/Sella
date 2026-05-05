<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}
$penjual_id = $_SESSION['id'];
$tahun_ini = date('Y');

// 1. Kueri Statistik Pendapatan per Bulan
$q_pendapatan = mysqli_query($conn, "
    SELECT DATE_FORMAT(o.tanggal_transaksi, '%b') as bulan, SUM(od.subtotal) as total_pendapatan 
    FROM order_details od 
    JOIN orders o ON od.order_id = o.id 
    JOIN products p ON od.produk_id = p.id 
    WHERE p.penjual_id = '$penjual_id' AND o.status_pesanan IN ('dikirim', 'selesai') AND YEAR(o.tanggal_transaksi) = '$tahun_ini' 
    GROUP BY MONTH(o.tanggal_transaksi)
");

$label_bulan = []; $data_pendapatan = [];
while($row = mysqli_fetch_assoc($q_pendapatan)) {
    $label_bulan[] = $row['bulan'];
    $data_pendapatan[] = $row['total_pendapatan'];
}

// 2. Kueri 5 Produk Paling Laris (Sering Dibeli)
$q_laris = mysqli_query($conn, "
    SELECT p.nama_produk, SUM(od.jumlah) as total_terjual 
    FROM order_details od 
    JOIN products p ON od.produk_id = p.id 
    JOIN orders o ON od.order_id = o.id 
    WHERE p.penjual_id = '$penjual_id' AND o.status_pesanan IN ('dikirim', 'selesai') 
    GROUP BY p.id ORDER BY total_terjual DESC LIMIT 5
");

$label_produk = []; $data_terjual = [];
while($row = mysqli_fetch_assoc($q_laris)) {
    $label_produk[] = substr($row['nama_produk'], 0, 15) . '...'; // Potong nama agar muat di grafik
    $data_terjual[] = $row['total_terjual'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Analitik Toko - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light pb-5">
<div class="container mt-5">
    <a href="dashboard_penjual.php" class="btn btn-outline-secondary mb-4">&larr; Kembali ke Dashboard</a>
    <h3 class="fw-bold mb-4">Grafik Analitik Penjualan (<?php echo $tahun_ini; ?>)</h3>
    
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">Tren Pendapatan Bulanan</div>
                <div class="card-body">
                    <canvas id="chartPendapatan" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">5 Produk Paling Laris</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartProdukLaris"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Konfigurasi Chart Pendapatan (Line Chart)
    const ctxPendapatan = document.getElementById('chartPendapatan').getContext('2d');
    new Chart(ctxPendapatan, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($label_bulan); ?>,
            datasets: [{
                label: 'Total Pendapatan (Rp)',
                data: <?php echo json_encode($data_pendapatan); ?>,
                borderColor: '#118a44',
                backgroundColor: 'rgba(17, 138, 68, 0.2)',
                borderWidth: 3,
                fill: true,
                tension: 0.3
            }]
        }
    });

    // Konfigurasi Chart Produk Terlaris (Doughnut Chart)
    const ctxLaris = document.getElementById('chartProdukLaris').getContext('2d');
    new Chart(ctxLaris, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($label_produk); ?>,
            datasets: [{
                data: <?php echo json_encode($data_terjual); ?>,
                backgroundColor: ['#ee4d2d', '#118a44', '#ffc107', '#0d6efd', '#6c757d']
            }]
        }
    });
</script>
</body>
</html>
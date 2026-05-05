<?php
session_start();
include 'koneksi.php';
if ($_SESSION['role'] !== 'penjual') { header("Location: login.php"); exit; }
$penjual_id = $_SESSION['id'];

// Proses Input Resi dan Ubah Status ke Dikirim
if(isset($_POST['kirim_order'])) {
    $id_order = $_POST['order_id'];
    $no_resi = mysqli_real_escape_string($conn, $_POST['no_resi']);
    
    mysqli_query($conn, "UPDATE orders SET status_pesanan = 'dikirim', no_resi = '$no_resi' WHERE id = '$id_order'");
    echo "<script>alert('Pesanan berhasil dikirim dengan Nomor Resi: $no_resi'); window.location='laporan_penjual.php';</script>";
}

// Ambil data pesanan (Saya tambahkan o.metode_pembayaran di sini)
$q = mysqli_query($conn, "
    SELECT od.*, o.status_pesanan, o.no_resi, o.tanggal_transaksi, o.metode_pembayaran, 
           p.nama_produk, u.username as pembeli, u.alamat_lengkap 
    FROM order_details od 
    JOIN orders o ON od.order_id = o.id 
    JOIN products p ON od.produk_id = p.id 
    JOIN users u ON o.pembeli_id = u.id 
    WHERE p.penjual_id = '$penjual_id' 
    ORDER BY o.tanggal_transaksi DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pesanan Masuk - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light pb-5">
<div class="container py-5">
    <a href="dashboard_penjual.php" class="btn btn-outline-secondary mb-3">&larr; Dashboard</a>
    <h3 class="fw-bold mb-4">Daftar Pesanan Masuk</h3>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle m-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID & Info</th>
                            <th>Pembeli & Alamat</th>
                            <th>Produk</th>
                            <th>Subtotal</th>
                            <th>Status & Resi</th>
                            <th>Aksi (Input Resi)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($q)): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary">#ORD-<?php echo $row['order_id']; ?></span><br>
                                <?php if($row['metode_pembayaran'] == 'cod'): ?>
                                    <span class="badge bg-danger mt-1">COD (Bayar di Tempat)</span>
                                <?php else: ?>
                                    <span class="badge bg-success mt-1">LUNAS (SELLA Pay)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo $row['pembeli']; ?></strong><br>
                                <small class="text-muted"><?php echo $row['alamat_lengkap']; ?></small>
                            </td>
                            <td>
                                <?php echo $row['nama_produk']; ?><br>
                                <small>Qty: <?php echo $row['jumlah']; ?></small>
                            </td>
                            <td class="text-success fw-bold">Rp <?php echo number_format($row['subtotal'],0,',','.'); ?></td>
                            <td>
                                <?php echo strtoupper($row['status_pesanan']); ?><br>
                                <?php if($row['no_resi']): ?>
                                    <small class="text-primary fw-bold">Resi: <?php echo $row['no_resi']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['status_pesanan'] == 'dikemas'): ?>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <input type="text" name="no_resi" class="form-control form-control-sm" placeholder="Input No. Resi..." required>
                                        <button type="submit" name="kirim_order" class="btn btn-sm btn-primary">Kirim</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-light border" disabled>Telah Diproses</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
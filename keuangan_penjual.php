<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];

$query_user = mysqli_query($conn, "SELECT saldo FROM users WHERE id = '$penjual_id'");
$user_data = mysqli_fetch_assoc($query_user);
$saldo_sekarang = $user_data['saldo'];

if (isset($_POST['tarik_dana'])) {
    $jumlah_tarik = $_POST['jumlah_tarik'];
    $bank = $_POST['bank'];
    $rekening = $_POST['rekening'];

    if ($jumlah_tarik > $saldo_sekarang) {
        echo "<script>alert('Saldo tidak mencukupi!');</script>";
    } elseif ($jumlah_tarik < 50000) {
        echo "<script>alert('Minimal penarikan adalah Rp 50.000');</script>";
    } else {
        mysqli_query($conn, "UPDATE users SET saldo = saldo - $jumlah_tarik WHERE id = '$penjual_id'");
        mysqli_query($conn, "INSERT INTO withdrawals (penjual_id, jumlah, nama_bank, nomor_rekening) VALUES ('$penjual_id', '$jumlah_tarik', '$bank', '$rekening')");
        echo "<script>alert('Permintaan penarikan berhasil dikirim ke Admin!'); window.location='keuangan_penjual.php';</script>";
    }
}

$q_riwayat = mysqli_query($conn, "SELECT * FROM withdrawals WHERE penjual_id = '$penjual_id' ORDER BY tanggal_request DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keuangan Toko - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="dashboard_penjual.php" class="btn btn-outline-secondary mb-3">&larr; Dashboard</a>
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center bg-primary text-white rounded">
                    <h5>Saldo Penghasilan Toko</h5>
                    <h1 class="fw-bold">Rp <?php echo number_format($saldo_sekarang, 0, ',', '.'); ?></h1>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold">Tarik Dana</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Jumlah Tarik (Min Rp 50.000)</label>
                            <input type="number" name="jumlah_tarik" class="form-control" max="<?php echo $saldo_sekarang; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Bank (BCA/Mandiri/BRI dll)</label>
                            <input type="text" name="bank" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Rekening</label>
                            <input type="text" name="rekening" class="form-control" required>
                        </div>
                        <button type="submit" name="tarik_dana" class="btn btn-primary w-100 fw-bold">Ajukan Penarikan</button>
                    </form>
                </div>
            </div>
            
            <h5 class="fw-bold mb-3">Riwayat Penarikan</h5>
            <div class="table-responsive bg-white rounded shadow-sm p-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($q_riwayat)): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_request'])); ?></td>
                            <td class="text-danger fw-bold">- Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                            <td>
                                <?php 
                                if($row['status'] == 'pending') echo "<span class='badge bg-warning text-dark'>Diproses Admin</span>";
                                elseif($row['status'] == 'disetujui') echo "<span class='badge bg-success'>Berhasil Ditransfer</span>";
                                else echo "<span class='badge bg-danger'>Ditolak (Refund)</span>";
                                ?>
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
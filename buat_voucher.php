<?php
session_start();
include 'koneksi.php';

// Pastikan yang mengakses adalah penjual
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') { 
    header("Location: login.php"); 
    exit; 
}

$penjual_id = $_SESSION['id'];

// Proses Pembuatan Voucher Baru
if (isset($_POST['simpan_voucher'])) {
    $kode = strtoupper(mysqli_real_escape_string($conn, $_POST['kode_voucher']));
    $tipe = $_POST['tipe_diskon'];
    $nilai = $_POST['nilai_diskon'];
    $min = $_POST['min_belanja'];
    $kuota = $_POST['kuota'];
    $tgl = $_POST['tanggal_berakhir'];

    // Validasi sederhana: Jika diskon persen, maksimal 100%
    if ($tipe == 'persen' && $nilai > 100) {
        echo "<script>alert('Diskon persentase tidak boleh lebih dari 100%!');</script>";
    } else {
        // Cek apakah kode voucher sudah pernah ada
        $cek_kode = mysqli_query($conn, "SELECT id FROM vouchers WHERE kode_voucher = '$kode'");
        if (mysqli_num_rows($cek_kode) > 0) {
            echo "<script>alert('Kode voucher sudah digunakan. Silakan buat kode lain (Contoh: SELLA99).');</script>";
        } else {
            $query = "INSERT INTO vouchers (penjual_id, kode_voucher, tipe_diskon, nilai_diskon, min_belanja, kuota, tanggal_berakhir) 
                      VALUES ('$penjual_id', '$kode', '$tipe', '$nilai', '$min', '$kuota', '$tgl')";
            
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Voucher berhasil dibuat dan sudah aktif!'); window.location='buat_voucher.php';</script>";
            } else {
                echo "<script>alert('Gagal membuat voucher!');</script>";
            }
        }
    }
}

// Ambil daftar voucher yang pernah dibuat penjual ini
$q_voucher = mysqli_query($conn, "SELECT * FROM vouchers WHERE penjual_id = '$penjual_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Manajemen Voucher - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-5 mb-4">
            <a href="dashboard_penjual.php" class="btn btn-outline-secondary mb-3">&larr; Kembali ke Dashboard</a>
            
            <div class="card shadow-sm border-0">
                <div class="card-header text-white fw-bold d-flex align-items-center" style="background-color: #118a44;">
                    <i class="bi bi-ticket-perforated me-2 fs-5"></i> Buat Voucher Baru
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode Voucher</label>
                            <input type="text" name="kode_voucher" class="form-control" placeholder="Contoh: HEMAT50K" maxlength="20" required>
                            <small class="text-muted">Gunakan huruf kapital dan angka tanpa spasi.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tipe Diskon</label>
                                <select name="tipe_diskon" class="form-select" required>
                                    <option value="nominal">Potongan Rupiah (Rp)</option>
                                    <option value="persen">Persentase (%)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Besar Diskon</label>
                                <input type="number" name="nilai_diskon" class="form-control" placeholder="Misal: 10 atau 50000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Minimal Belanja (Rp)</label>
                            <input type="number" name="min_belanja" class="form-control" value="0" required>
                            <small class="text-muted">Isi 0 jika tanpa minimal belanja.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kuota Penggunaan</label>
                                <input type="number" name="kuota" class="form-control" placeholder="Contoh: 100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Berlaku Hingga</label>
                                <input type="date" name="tanggal_berakhir" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="simpan_voucher" class="btn text-white w-100 fw-bold py-2 mt-2" style="background-color: #118a44;">
                            Aktifkan Voucher
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <h4 class="fw-bold mb-3 mt-md-5 mt-0">Daftar Voucher Toko Anda</h4>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Diskon</th>
                                    <th>Min. Belanja</th>
                                    <th>Sisa Kuota</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($q_voucher) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($q_voucher)): 
                                        $tgl_sekarang = date('Y-m-d');
                                        // Tentukan status expired atau habis
                                        $status = "Aktif";
                                        $badge_color = "success";
                                        
                                        if ($row['tanggal_berakhir'] < $tgl_sekarang) {
                                            $status = "Kedaluwarsa";
                                            $badge_color = "danger";
                                        } elseif ($row['kuota'] <= 0) {
                                            $status = "Habis";
                                            $badge_color = "secondary";
                                        }
                                    ?>
                                    <tr>
                                        <td><span class="badge bg-dark fs-6"><?php echo $row['kode_voucher']; ?></span></td>
                                        <td class="fw-bold text-success">
                                            <?php 
                                            if($row['tipe_diskon'] == 'nominal') echo "Rp " . number_format($row['nilai_diskon'],0,',','.');
                                            else echo $row['nilai_diskon'] . "%";
                                            ?>
                                        </td>
                                        <td>Rp <?php echo number_format($row['min_belanja'],0,',','.'); ?></td>
                                        <td class="text-center fw-bold"><?php echo $row['kuota']; ?></td>
                                        <td><span class="badge bg-<?php echo $badge_color; ?>"><?php echo $status; ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada voucher yang dibuat.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
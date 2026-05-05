<?php
session_start();
include 'koneksi.php';

// Pastikan hanya penjual yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') { 
    header("Location: login.php"); 
    exit; 
}
$penjual_id = $_SESSION['id'];

// Proses simpan Flash Sale
if (isset($_POST['simpan_flash_sale'])) {
    $produk_id = $_POST['produk_id'];
    $harga_diskon = $_POST['harga_diskon'];
    $waktu_berakhir = $_POST['waktu_berakhir']; // Mengambil format dari form HTML datetime-local

    // Update data produk di database
    $query = "UPDATE products SET harga_flash_sale = '$harga_diskon', flash_sale_end = '$waktu_berakhir' WHERE id = '$produk_id' AND penjual_id = '$penjual_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Flash Sale berhasil diaktifkan pada produk tersebut!'); window.location='dashboard_penjual.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat mengatur Flash Sale.');</script>";
    }
}

// Ambil daftar produk milik penjual ini untuk ditampilkan di pilihan dropdown
$q_produk = mysqli_query($conn, "SELECT id, nama_produk, harga FROM products WHERE penjual_id = '$penjual_id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Atur Flash Sale - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <a href="dashboard_penjual.php" class="btn btn-outline-secondary mb-3">&larr; Kembali ke Dashboard</a>
            
            <div class="card shadow-sm border-0">
                <div class="card-header text-white fw-bold d-flex align-items-center" style="background-color: #ee4d2d;">
                    <i class="bi bi-lightning-charge-fill me-2 fs-5"></i> Buat Flash Sale Toko
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Pilih produk Anda dan atur harga diskon beserta waktu berakhirnya. Produk ini akan muncul di urutan teratas pada halaman pembeli!</p>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Produk</label>
                            <select name="produk_id" class="form-select" required>
                                <option value="">-- Pilih Produk Anda --</option>
                                <?php while($p = mysqli_fetch_assoc($q_produk)): ?>
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo $p['nama_produk']; ?> (Harga Asli: Rp <?php echo number_format($p['harga'],0,',','.'); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Harga Diskon Flash Sale (Rp)</label>
                            <input type="number" name="harga_diskon" class="form-control" placeholder="Contoh: 45000" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Waktu Berakhir Diskon</label>
                            <input type="datetime-local" name="waktu_berakhir" class="form-control" required>
                            <small class="text-danger mt-1 d-block">*Pastikan memilih waktu di masa depan.</small>
                        </div>
                        
                        <button type="submit" name="simpan_flash_sale" class="btn text-white w-100 fw-bold py-2" style="background-color: #ee4d2d;">
                            Mulai Flash Sale Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
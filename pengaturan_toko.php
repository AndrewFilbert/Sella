<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];

// Ambil data toko saat ini
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$penjual_id'");
$toko = mysqli_fetch_assoc($query);

// Ubah string kurir_aktif dari database (contoh: "jne,tiki") menjadi array
$kurir_array = explode(',', $toko['kurir_aktif']);

if (isset($_POST['simpan'])) {
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_toko']);
    
    // Gabungkan array kurir yang dicentang menjadi string (contoh: "jne,pos")
    $kurir_dipilih = isset($_POST['kurir']) ? implode(',', $_POST['kurir']) : '';

    mysqli_query($conn, "UPDATE users SET deskripsi_toko = '$deskripsi', kurir_aktif = '$kurir_dipilih' WHERE id = '$penjual_id'");
    echo "<script>alert('Pengaturan Toko & Kurir berhasil diperbarui!'); window.location='pengaturan_toko.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengaturan Toko - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light pb-5">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="dashboard_penjual.php" class="btn btn-outline-secondary mb-3">&larr; Kembali ke Dashboard</a>
            
            <div class="card shadow-sm border-0">
                <div class="card-header text-white fw-bold" style="background-color: #118a44;">Pengaturan Toko & Pengiriman</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Deskripsi Toko</label>
                            <textarea name="deskripsi_toko" class="form-control" rows="4" placeholder="Ceritakan tentang toko Anda..."><?php echo $toko['deskripsi_toko'] ?? ''; ?></textarea>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3">Opsi Kurir Pengiriman Aktif</h6>
                        <p class="text-muted small">Centang kurir yang ingin Anda sediakan untuk pembeli saat checkout. (Minimal pilih satu)</p>
                        
                        <div class="d-flex gap-4 mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kurir[]" value="jne" id="jne" <?php if(in_array('jne', $kurir_array)) echo 'checked'; ?>>
                                <label class="form-check-label fw-bold" for="jne">JNE</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kurir[]" value="pos" id="pos" <?php if(in_array('pos', $kurir_array)) echo 'checked'; ?>>
                                <label class="form-check-label fw-bold" for="pos">POS Indonesia</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kurir[]" value="tiki" id="tiki" <?php if(in_array('tiki', $kurir_array)) echo 'checked'; ?>>
                                <label class="form-check-label fw-bold" for="tiki">TIKI</label>
                            </div>
                        </div>

                        <button type="submit" name="simpan" class="btn w-100 text-white fw-bold py-2" style="background-color: #118a44;">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
session_start();
include 'koneksi.php';

// Pastikan yang akses adalah pembeli dan ada ID pesanan serta ID produk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli' || !isset($_GET['produk_id']) || !isset($_GET['order_id'])) {
    header("Location: pesanan_saya.php");
    exit;
}

$produk_id = $_GET['produk_id'];
$order_id = $_GET['order_id'];
$pembeli_id = $_SESSION['id'];

// Cek apakah produk ini sudah pernah diulas di pesanan ini (mencegah double ulasan)
$cek_ulasan = mysqli_query($conn, "SELECT id FROM reviews WHERE order_id = '$order_id' AND produk_id = '$produk_id'");
if (mysqli_num_rows($cek_ulasan) > 0) {
    echo "<script>alert('Anda sudah memberikan ulasan untuk produk ini!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Ambil info produk untuk ditampilkan di halaman form
$q_produk = mysqli_query($conn, "SELECT nama_produk, gambar FROM products WHERE id = '$produk_id'");
$info_produk = mysqli_fetch_assoc($q_produk);

// Proses kirim ulasan
if (isset($_POST['kirim_ulasan'])) {
    $rating = $_POST['rating'];
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar']); // Mengamankan input teks

    $query = "INSERT INTO reviews (produk_id, pembeli_id, order_id, rating, komentar) 
              VALUES ('$produk_id', '$pembeli_id', '$order_id', '$rating', '$komentar')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Terima kasih atas ulasan Anda!'); window.location='pesanan_saya.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Beri Ulasan - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <a href="pesanan_saya.php" class="btn btn-outline-secondary mb-3">&larr; Kembali ke Pesanan Saya</a>
            
            <div class="card shadow-sm border-0">
                <div class="card-header text-white fw-bold d-flex align-items-center" style="background-color: #118a44;">
                    <i class="bi bi-star-fill me-2 text-warning"></i> Nilai Kualitas Produk
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <img src="uploads/<?php echo $info_produk['gambar']; ?>" width="60" height="60" class="rounded me-3" style="object-fit: cover;">
                        <div>
                            <h6 class="m-0 fw-bold"><?php echo $info_produk['nama_produk']; ?></h6>
                            <small class="text-muted">Bagaimana pendapat Anda tentang barang ini?</small>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Beri Nilai (Bintang)</label>
                            <select name="rating" class="form-select text-warning fw-bold" required>
                                <option value="5" selected>⭐⭐⭐⭐⭐ (5 - Sangat Bagus)</option>
                                <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                                <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                <option value="2">⭐⭐ (2 - Kurang)</option>
                                <option value="1">⭐ (1 - Sangat Buruk)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Tulis Komentar Anda</label>
                            <textarea name="komentar" class="form-control" rows="5" placeholder="Ceritakan pengalaman Anda menggunakan produk ini. Apakah kualitasnya sesuai dengan deskripsi?" required></textarea>
                        </div>

                        <button type="submit" name="kirim_ulasan" class="btn w-100 text-white fw-bold py-2" style="background-color: #118a44;">
                            Kirim Ulasan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
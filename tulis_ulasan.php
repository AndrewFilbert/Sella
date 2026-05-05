<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$pembeli_id = $_SESSION['id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Pastikan pesanan ini valid dan sudah selesai
$cek_order = mysqli_query($conn, "SELECT id FROM orders WHERE id = '$order_id' AND pembeli_id = '$pembeli_id' AND status_pesanan = 'selesai'");
if (mysqli_num_rows($cek_order) == 0) {
    echo "<script>alert('Pesanan tidak valid atau belum diselesaikan!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Proses Simpan Ulasan ke Database
if (isset($_POST['simpan_ulasan'])) {
    foreach ($_POST['bintang'] as $produk_id => $bintang) {
        $komentar = mysqli_real_escape_string($conn, $_POST['komentar'][$produk_id]);
        $bintang = (int)$bintang;

        // Cek apakah produk ini sudah pernah diulas di pesanan ini
        $cek_ulasan = mysqli_query($conn, "SELECT id FROM reviews WHERE order_id = '$order_id' AND produk_id = '$produk_id'");
        if (mysqli_num_rows($cek_ulasan) == 0) {
            mysqli_query($conn, "INSERT INTO reviews (pembeli_id, produk_id, order_id, bintang, komentar) 
                                 VALUES ('$pembeli_id', '$produk_id', '$order_id', '$bintang', '$komentar')");
        }
    }
    echo "<script>alert('Terima kasih! Ulasan Anda telah diterbitkan.'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Ambil barang-barang yang ada di pesanan ini
$q_detail = mysqli_query($conn, "SELECT od.*, p.nama_produk, p.gambar FROM order_details od JOIN products p ON od.produk_id = p.id WHERE od.order_id = '$order_id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Ulasan - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        .top-nav { background: #fff; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .review-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px; }
        .prod-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px; }
        .rating-box { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 10px; margin-top: 10px; }
        .rating-box input { display: none; }
        .rating-box label { font-size: 30px; color: #ddd; cursor: pointer; transition: 0.2s; }
        .rating-box input:checked ~ label, .rating-box label:hover, .rating-box label:hover ~ label { color: #ffc107; }
        .container-custom { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>

<div class="top-nav mb-4">
    <div class="container container-custom d-flex align-items-center">
        <a href="pesanan_saya.php" class="text-dark fs-4 me-3"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Nilai Produk</h5>
    </div>
</div>

<div class="container container-custom">
    <form action="" method="POST">
        <?php while ($detail = mysqli_fetch_assoc($q_detail)): $pid = $detail['produk_id']; ?>
        <div class="review-card">
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                <img src="uploads/<?php echo $detail['gambar']; ?>" class="prod-img">
                <div class="fw-bold fs-6"><?php echo $detail['nama_produk']; ?></div>
            </div>
            
            <label class="fw-bold mb-2">Kualitas Produk</label>
            <div class="rating-box mb-3">
                <input type="radio" id="star5_<?php echo $pid; ?>" name="bintang[<?php echo $pid; ?>]" value="5" required>
                <label for="star5_<?php echo $pid; ?>"><i class="bi bi-star-fill"></i></label>
                <input type="radio" id="star4_<?php echo $pid; ?>" name="bintang[<?php echo $pid; ?>]" value="4">
                <label for="star4_<?php echo $pid; ?>"><i class="bi bi-star-fill"></i></label>
                <input type="radio" id="star3_<?php echo $pid; ?>" name="bintang[<?php echo $pid; ?>]" value="3">
                <label for="star3_<?php echo $pid; ?>"><i class="bi bi-star-fill"></i></label>
                <input type="radio" id="star2_<?php echo $pid; ?>" name="bintang[<?php echo $pid; ?>]" value="2">
                <label for="star2_<?php echo $pid; ?>"><i class="bi bi-star-fill"></i></label>
                <input type="radio" id="star1_<?php echo $pid; ?>" name="bintang[<?php echo $pid; ?>]" value="1">
                <label for="star1_<?php echo $pid; ?>"><i class="bi bi-star-fill"></i></label>
            </div>

            <textarea name="komentar[<?php echo $pid; ?>]" class="form-control bg-light" rows="3" placeholder="Ceritakan kepuasan Anda terhadap barang ini... (Opsional)"></textarea>
        </div>
        <?php endwhile; ?>

        <button type="submit" name="simpan_ulasan" class="btn btn-success w-100 fw-bold py-3 rounded-pill shadow-sm mb-5">Kirim Ulasan</button>
    </form>
</div>

</body>
</html>
<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['id'])) { header("Location: marketplace_pembeli.php"); exit; }

$id_toko = $_GET['id'];
$q_toko = mysqli_query($conn, "SELECT shop_profiles.*, users.username FROM users LEFT JOIN shop_profiles ON users.id = shop_profiles.penjual_id WHERE users.id = '$id_toko'");
$toko = mysqli_fetch_assoc($q_toko);

$nama_toko = $toko['nama_toko'] ?? $toko['username'];
$deskripsi = $toko['deskripsi_toko'] ?? "Selamat datang di toko kami. Kami menjual produk berkualitas.";
$logo = $toko['logo_toko'] ? "uploads/" . $toko['logo_toko'] : "https://via.placeholder.com/150";
$banner = $toko['banner_toko'] ? "uploads/" . $toko['banner_toko'] : "https://via.placeholder.com/1200x300?text=Banner+Toko+SELLA";

$q_produk = mysqli_query($conn, "SELECT * FROM products WHERE penjual_id = '$id_toko' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Toko <?php echo $nama_toko; ?> - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .banner { height: 250px; width: 100%; object-fit: cover; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; }
        .logo-profil { width: 100px; height: 100px; object-fit: cover; border: 4px solid white; border-radius: 50%; margin-top: -50px; background: white; }
    </style>
</head>
<body class="bg-light pb-5">

<nav class="navbar navbar-dark sticky-top shadow-sm" style="background-color: #118a44;">
    <div class="container">
        <a class="navbar-brand fw-bold fs-5" href="marketplace_pembeli.php"><i class="bi bi-arrow-left"></i> Kembali ke SELLA</a>
    </div>
</nav>

<div class="container mb-5">
    <div class="bg-white rounded shadow-sm pb-4 text-center">
        <img src="<?php echo $banner; ?>" class="banner img-fluid">
        <img src="<?php echo $logo; ?>" class="logo-profil shadow-sm">
        <h3 class="fw-bold mt-2 mb-1"><?php echo $nama_toko; ?></h3>
        <p class="text-muted px-4" style="max-width: 600px; margin: 0 auto;"><?php echo nl2br($deskripsi); ?></p>
        <a href="chat.php?lawan_id=<?php echo $id_toko; ?>" class="btn btn-outline-success mt-3 rounded-pill px-4"><i class="bi bi-chat-dots"></i> Chat Penjual</a>
    </div>
</div>

<div class="container">
    <h5 class="fw-bold mb-3 border-bottom pb-2">Semua Etalase Toko</h5>
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3">
        <?php if(mysqli_num_rows($q_produk) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($q_produk)): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="uploads/<?php echo $row['gambar']; ?>" class="card-img-top" style="height: 160px; object-fit: cover;">
                    <div class="card-body p-2 d-flex flex-column text-center">
                        <h6 class="text-truncate small"><?php echo $row['nama_produk']; ?></h6>
                        <p class="fw-bold text-success mb-2">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        <a href="detail_produk.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success w-100 mt-auto">Beli</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-5">Penjual ini belum memiliki produk.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
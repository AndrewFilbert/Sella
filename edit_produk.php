<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$id_produk = $_GET['id'];
$penjual_id = $_SESSION['id'];

// --- PROSES HAPUS VARIAN ---
// Jika penjual menekan tombol "Hapus" pada salah satu varian yang sudah ada
if (isset($_GET['hapus_varian'])) {
    $id_v = $_GET['hapus_varian'];
    mysqli_query($conn, "DELETE FROM product_variants WHERE id = '$id_v'");
    echo "<script>alert('Varian berhasil dihapus!'); window.location='edit_produk.php?id=$id_produk';</script>";
    exit;
}

// --- AMBIL DATA PRODUK & VARIAN ---
$q_prod = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id_produk' AND penjual_id = '$penjual_id'");
$produk = mysqli_fetch_assoc($q_prod);
if (!$produk) { die("Produk tidak ditemukan atau bukan milik Anda."); }

$q_varian = mysqli_query($conn, "SELECT * FROM product_variants WHERE produk_id = '$id_produk'");

// --- PROSES SIMPAN PERUBAHAN ---
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga_base = $_POST['harga'];
    $stok_base = $_POST['stok'];
    $berat = $_POST['berat'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // 1. Update Foto Jika Ada yang Baru
    if ($_FILES['gambar']['name'] != '') {
        $gambar = time() . "_" . $_FILES['gambar']['name'];
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/" . basename($gambar))) {
            mysqli_query($conn, "UPDATE products SET gambar='$gambar' WHERE id='$id_produk'");
        }
    }

    // 2. Update Data Produk Utama
    mysqli_query($conn, "UPDATE products SET nama_produk='$nama', harga='$harga_base', stok='$stok_base', berat='$berat', deskripsi='$deskripsi' WHERE id='$id_produk'");

    // 3. Update Data Varian LAMA (Jika ada)
    if (isset($_POST['edit_v_id'])) {
        foreach ($_POST['edit_v_id'] as $key => $v_id) {
            $nama_v = mysqli_real_escape_string($conn, $_POST['edit_nama_v'][$key]);
            $harga_v = $_POST['edit_harga_v'][$key];
            $stok_v = $_POST['edit_stok_v'][$key];
            mysqli_query($conn, "UPDATE product_variants SET nama_varian='$nama_v', harga_tambahan='$harga_v', stok_varian='$stok_v' WHERE id='$v_id'");
        }
    }

    // 4. Insert Varian BARU (Jika penjual menekan tombol +Tambah Varian Baru)
    if (isset($_POST['nama_varian'])) {
        foreach ($_POST['nama_varian'] as $key => $val) {
            $nama_baru = mysqli_real_escape_string($conn, $val);
            $harga_baru = $_POST['harga_varian'][$key];
            $stok_baru = $_POST['stok_varian'][$key];
            if ($nama_baru != "") {
                mysqli_query($conn, "INSERT INTO product_variants (produk_id, nama_varian, harga_tambahan, stok_varian) VALUES ('$id_produk', '$nama_baru', '$harga_baru', '$stok_baru')");
            }
        }
    }

    echo "<script>alert('Perubahan produk & varian berhasil disimpan!'); window.location='dashboard_penjual.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Produk - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light pb-5">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <a href="dashboard_penjual.php" class="btn btn-outline-secondary mb-3">&larr; Batal</a>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header text-white fw-bold" style="background-color: #118a44;">Edit Produk Utama</div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Produk</label>
                            <input type="text" name="nama_produk" class="form-control" value="<?php echo $produk['nama_produk']; ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Harga Dasar (Rp)</label>
                                <input type="number" name="harga" class="form-control" value="<?php echo $produk['harga']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Stok Utama</label>
                                <input type="number" name="stok" class="form-control" value="<?php echo $produk['stok']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Berat (Gram)</label>
                                <input type="number" name="berat" class="form-control" value="<?php echo $produk['berat']; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="4" required><?php echo $produk['deskripsi']; ?></textarea>
                        </div>
                        
                        <div class="mb-0 d-flex align-items-center">
                            <img src="uploads/<?php echo $produk['gambar']; ?>" width="80" class="rounded me-3 border">
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold mb-1">Ganti Foto (Opsional)</label>
                                <input type="file" name="gambar" class="form-control" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(mysqli_num_rows($q_varian) > 0): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-warning text-dark fw-bold">Varian Saat Ini</div>
                    <div class="card-body bg-light">
                        <?php while($v = mysqli_fetch_assoc($q_varian)): ?>
                            <div class="row g-2 mb-2 align-items-end">
                                <input type="hidden" name="edit_v_id[]" value="<?php echo $v['id']; ?>">
                                <div class="col-md-4">
                                    <label class="small fw-bold">Nama Varian</label>
                                    <input type="text" name="edit_nama_v[]" class="form-control form-control-sm" value="<?php echo $v['nama_varian']; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">Harga Tambahan (Rp)</label>
                                    <input type="number" name="edit_harga_v[]" class="form-control form-control-sm" value="<?php echo $v['harga_tambahan']; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">Stok</label>
                                    <input type="number" name="edit_stok_v[]" class="form-control form-control-sm" value="<?php echo $v['stok_varian']; ?>">
                                </div>
                                <div class="col-md-2">
                                    <a href="?id=<?php echo $id_produk; ?>&hapus_varian=<?php echo $v['id']; ?>" class="btn btn-danger btn-sm w-100" onclick="return confirm('Yakin ingin menghapus varian ini?');">Hapus</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                        Tambah Varian Baru
                        <button type="button" id="add-variant" class="btn btn-sm btn-success">+ Tambah Form</button>
                    </div>
                    <div class="card-body">
                        <div id="variant-container">
                            </div>
                    </div>
                </div>

                <button type="submit" name="update" class="btn w-100 text-white fw-bold py-3" style="background-color: #118a44;">Simpan Semua Perubahan</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#add-variant").click(function() {
        var html = `
            <div class="row g-2 mb-3 variant-row align-items-end">
                <div class="col-md-4">
                    <label class="small fw-bold text-success">Nama Varian Baru</label>
                    <input type="text" name="nama_varian[]" class="form-control" placeholder="cth: Biru - M" required>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-success">Harga Tambahan</label>
                    <input type="number" name="harga_varian[]" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-success">Stok Varian</label>
                    <input type="number" name="stok_varian[]" class="form-control" value="1">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-variant w-100">Batal Tambah</button>
                </div>
            </div>`;
        $("#variant-container").append(html);
    });

    $(document).on('click', '.remove-variant', function() {
        $(this).closest('.variant-row').remove();
    });
});
</script>
</body>
</html>
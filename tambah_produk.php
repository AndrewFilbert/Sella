<?php
session_start();
include 'koneksi.php';

// Validasi akses hanya untuk penjual
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login.php"); exit;
}

$penjual_id = $_SESSION['id'];

if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $berat = (int)$_POST['berat'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // 1. Upload Gambar Utama
    $nama_gambar_utama = $_FILES['gambar_utama']['name'];
    $tmp_gambar_utama = $_FILES['gambar_utama']['tmp_name'];
    
    // Rename gambar agar tidak bentrok
    $ext_utama = pathinfo($nama_gambar_utama, PATHINFO_EXTENSION);
    $gambar_utama_baru = 'main_' . time() . '.' . $ext_utama;
    move_uploaded_file($tmp_gambar_utama, 'uploads/' . $gambar_utama_baru);

    // 2. Insert data produk ke tabel utama
    $query_insert = "INSERT INTO products (penjual_id, nama_produk, kategori, harga, stok, berat, deskripsi, gambar) 
                     VALUES ('$penjual_id', '$nama', '$kategori', '$harga', '$stok', '$berat', '$deskripsi', '$gambar_utama_baru')";
    
    if (mysqli_query($conn, $query_insert)) {
        $produk_id = mysqli_insert_id($conn); // Ambil ID produk yang baru saja dibuat

        // 3. Proses Upload Banyak Gambar (Gambar Tambahan)
        if (!empty($_FILES['gambar_tambahan']['name'][0])) {
            $jumlah_gambar = count($_FILES['gambar_tambahan']['name']);
            
            for ($i = 0; $i < $jumlah_gambar; $i++) {
                $nama_file = $_FILES['gambar_tambahan']['name'][$i];
                $tmp_file = $_FILES['gambar_tambahan']['tmp_name'][$i];
                
                if ($nama_file != "") {
                    $ext_tambahan = pathinfo($nama_file, PATHINFO_EXTENSION);
                    $gambar_tambahan_baru = 'sub_' . time() . '_' . $i . '.' . $ext_tambahan;
                    
                    if (move_uploaded_file($tmp_file, 'uploads/' . $gambar_tambahan_baru)) {
                        // Masukkan ke tabel product_images
                        mysqli_query($conn, "INSERT INTO product_images (produk_id, gambar) VALUES ('$produk_id', '$gambar_tambahan_baru')");
                    }
                }
            }
        }
        
        echo "<script>alert('Produk berhasil ditambahkan beserta gambar tambahannya!'); window.location='dashboard_penjual.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan produk!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Courier New', Courier, monospace; 
            padding-bottom: 50px; 
        }
        .form-card { 
            background: #fff; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            max-width: 800px; 
            margin: 30px auto; 
        }
        .form-label { font-weight: bold; color: #333; }
        .form-control, .form-select { border-radius: 8px; }
        .upload-area { 
            border: 2px dashed #118a44; 
            padding: 20px; 
            border-radius: 10px; 
            text-align: center; 
            background: #e8f5e9; 
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0"><i class="bi bi-box-seam text-success me-2"></i>Tambah Produk Baru</h4>
            <a href="dashboard_penjual.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        <!-- PASTIKAN ADA enctype="multipart/form-data" AGAR BISA UPLOAD GAMBAR -->
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama_produk" class="form-control" required placeholder="Contoh: Sepatu Sneakers Pria">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <option value="pakaian">Pakaian</option>
                        <option value="sepatu">Sepatu</option>
                        <option value="elektronik">Elektronik</option>
                        <option value="makanan">Makanan & Minuman</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" required placeholder="150000">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stok Awal</label>
                    <input type="number" name="stok" class="form-control" required placeholder="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Berat (Gram)</label>
                    <input type="number" name="berat" class="form-control" required placeholder="500">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Deskripsi Lengkap</label>
                <textarea name="deskripsi" class="form-control" rows="5" required placeholder="Jelaskan detail bahan, ukuran, dan keunggulan produk Anda..."></textarea>
            </div>

            <!-- BAGIAN UPLOAD GAMBAR -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="upload-area">
                        <i class="bi bi-image fs-1 text-success"></i>
                        <h6 class="fw-bold mt-2">Gambar Utama (Wajib)</h6>
                        <p class="small text-muted mb-2">Ini akan menjadi sampul depan produk.</p>
                        <input type="file" name="gambar_utama" class="form-control form-control-sm" accept="image/*" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="upload-area" style="background: #f8f9fa; border-color: #adb5bd;">
                        <i class="bi bi-images fs-1 text-secondary"></i>
                        <h6 class="fw-bold mt-2">Gambar Tambahan (Opsional)</h6>
                        <p class="small text-muted mb-2">Bisa pilih lebih dari 1 gambar sekaligus.</p>
                        <!-- ATRIBUT MULTIPLE SANGAT PENTING -->
                        <input type="file" name="gambar_tambahan[]" class="form-control form-control-sm" accept="image/*" multiple>
                    </div>
                </div>
            </div>

            <hr class="mb-4">
            <button type="submit" name="simpan" class="btn btn-success w-100 fw-bold py-2 fs-5 rounded-pill shadow-sm">
                <i class="bi bi-cloud-arrow-up me-2"></i> Upload Produk
            </button>
        </form>
    </div>
</div>

</body>
</html>
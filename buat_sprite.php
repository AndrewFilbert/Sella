<?php
// Script Ajaib Pembuat Spritesheet Otomatis!
$lebar_frame = 64;
$tinggi_frame = 64;
$kolom = 6;
$baris = 6;
$lebar_total = $lebar_frame * $kolom;
$tinggi_total = $tinggi_frame * $baris;

// Buat kanvas gambar
$image = imagecreatetruecolor($lebar_total, $tinggi_total);

// Set background transparan
imagesavealpha($image, true);
$trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $trans_colour);

// Siapkan palet warna karakter
$warna_idle = imagecolorallocate($image, 17, 138, 68);    // Hijau SELLA
$warna_walk = imagecolorallocate($image, 250, 89, 29);    // Oranye 
$warna_jump = imagecolorallocate($image, 220, 53, 69);    // Merah

// Mulai menggambar frame per frame
for($y = 0; $y < $baris; $y++) {
    for($x = 0; $x < $kolom; $x++) {
        $x_pos = $x * $lebar_frame;
        $y_pos = $y * $tinggi_frame;

        // Ganti warna berdasarkan baris animasi
        if ($y == 0) $warna = $warna_idle;
        else if ($y == 1) $warna = $warna_walk;
        else $warna = $warna_jump;

        // Gambar Badan Karakter (Kotak)
        imagefilledrectangle($image, $x_pos + 12, $y_pos + 12, $x_pos + 52, $y_pos + 64, $warna);
        
        // Gambar Mata (Biar terlihat menghadap kanan)
        $warna_mata = imagecolorallocate($image, 255, 255, 255);
        $warna_pupil = imagecolorallocate($image, 0, 0, 0);
        // Putih mata
        imagefilledrectangle($image, $x_pos + 38, $y_pos + 20, $x_pos + 46, $y_pos + 28, $warna_mata);
        // Pupil mata
        imagefilledrectangle($image, $x_pos + 42, $y_pos + 22, $x_pos + 46, $y_pos + 26, $warna_pupil);
    }
}

// Cek folder uploads
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

// Simpan file gambarnya!
$path = 'uploads/spritesheet.png';
imagepng($image, $path);
imagedestroy($image);

echo "<div style='font-family: Courier New; text-align: center; margin-top: 50px;'>";
echo "<h1 style='color: green;'>Bim Salabim! ✨</h1>";
echo "<h3>File <b>spritesheet.png</b> berhasil diciptakan di folder uploads/.</h3>";
echo "<p>Sekarang coba buka halaman game_koin.php Anda, karakternya pasti sudah hidup!</p>";
echo "<a href='dompet_pembeli.php' style='padding: 10px 20px; background: #118a44; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Dompet</a>";
echo "</div>";
?>
<?php
$kurir = isset($_POST['kurir']) ? $_POST['kurir'] : '';
$berat = isset($_POST['berat']) ? (int)$_POST['berat'] : 1000; // Default 1kg

// Tarif dasar per kilogram (Simulasi)
$tarif_per_kg = 15000; 

if ($kurir == 'jne') {
    $tarif_per_kg = 22000;
} elseif ($kurir == 'tiki') {
    $tarif_per_kg = 25000;
} elseif ($kurir == 'pos') {
    $tarif_per_kg = 18000;
}

// Hitung berat aktual (pembulatan ke atas per 1000 gram / 1 kg)
$kg = ceil($berat / 1000);
if ($kg == 0) $kg = 1;

$total_ongkir = $tarif_per_kg * $kg;

// Kembalikan angka total ongkir ke Javascript
echo $total_ongkir;
?>
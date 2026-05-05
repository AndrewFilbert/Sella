<?php
$host = "sql305.infinityfree.com"; // ganti dengan Database Host dari InfinityFree
$user = "if0_xxxxxxxx";            // ganti dengan Username database
$pass = "passwordkamu";            // ganti dengan Password database
$db   = "if0_xxxxxxxx_sella_db";   // ganti dengan nama Database

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
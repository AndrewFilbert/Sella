<?php
$prov_id = isset($_POST['prov_id']) ? $_POST['prov_id'] : '';
echo "<option value=''>-- Pilih Kota/Kabupaten --</option>";

if ($prov_id == '1') {
    echo "<option value='101'>Pekanbaru</option>";
    echo "<option value='102'>Dumai</option>";
    echo "<option value='103'>Kampar</option>";
} elseif ($prov_id == '2') {
    echo "<option value='201'>Padang</option>";
    echo "<option value='202'>Bukittinggi</option>";
    echo "<option value='203'>Payakumbuh</option>";
} elseif ($prov_id == '3') {
    echo "<option value='301'>Jakarta Selatan</option>";
    echo "<option value='302'>Jakarta Pusat</option>";
} elseif ($prov_id != '') {
    echo "<option value='999'>Kota Lainnya</option>";
}
?>
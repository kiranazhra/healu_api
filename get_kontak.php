<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = mysqli_connect("localhost", "root", "", "db_healu"); // Sesuaikan nama database

$id_pasien = $_POST['id_pasien'];
$query = mysqli_query($konek, "SELECT * FROM kontak_darurat WHERE id_pasien = '$id_pasien'");

$data = array();
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}
echo json_encode($data);
?>

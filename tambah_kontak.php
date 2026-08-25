<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = mysqli_connect("localhost", "root", "", "db_healu");

$id_pasien = $_POST['id_pasien'];
$nama = $_POST['nama_kontak'];
$nomor = $_POST['nomor_telepon'];

$query = mysqli_query($konek, "INSERT INTO kontak_darurat (id_pasien, nama_kontak, nomor_telepon) VALUES ('$id_pasien', '$nama', '$nomor')");

if ($query) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>

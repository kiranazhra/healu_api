<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$id_pasien = $_POST['id_pasien'];
$nama_obat = $_POST['nama_obat'];
$dosis = $_POST['dosis'];
$waktu_minum = $_POST['waktu_minum'];
$catatan = $_POST['catatan'];

$query = mysqli_query($konek, "INSERT INTO reminder_obat (id_pasien, nama_obat, dosis, waktu_minum, catatan) 
         VALUES ('$id_pasien', '$nama_obat', '$dosis', '$waktu_minum', '$catatan')");

if($query){
    echo json_encode(array("status" => "success"));
} else {
    echo json_encode(array("status" => "error"));
}
?>

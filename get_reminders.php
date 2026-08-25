<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';
$id_pasien = $_GET['id_pasien'];

$query = mysqli_query($konek, "SELECT * FROM reminder_obat WHERE id_pasien = '$id_pasien' ORDER BY id DESC");
$result = array();

while ($row = mysqli_fetch_assoc($query)) {
    $result[] = $row;
}

echo json_encode(array("status" => "success", "data" => $result));
?>

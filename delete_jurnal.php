<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

// Ambil ID dari Flutter
$id = $_POST['id_jurnal'] ?? '';

if (!empty($id)) {
    // Menghapus baris berdasarkan ID unik
    $query = "DELETE FROM monitoring_mental WHERE id = '$id'";
    if (mysqli_query($konek, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($konek)]);
    }
}
?>

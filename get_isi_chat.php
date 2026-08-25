<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

// Pastikan parameter id_konsultasi ada
if (isset($_GET['id_konsultasi'])) {
    $id_konsultasi = $_GET['id_konsultasi'];

    // 🟢 PERBAIKAN: Mengubah id_isi_chat menjadi id sesuai dengan di phpMyAdmin
    $query = "SELECT * FROM isi_chat WHERE id_konsultasi = '$id_konsultasi' ORDER BY id ASC";
    
    // 🟢 PERBAIKAN: Mengubah $connect menjadi $konek
    $result = mysqli_query($konek, $query);

    $response = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
        echo json_encode(array("status" => "success", "data" => $response));
    } else {
        echo json_encode(array("status" => "error", "message" => "Query gagal: " . mysqli_error($konek)));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Parameter id_konsultasi tidak ditemukan"));
}
?>

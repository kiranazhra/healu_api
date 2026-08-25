<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php'; 
$db = $konek; 

if (!$db) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal."]);
    exit;
}

// Menangkap ID pasien (user) yang sedang login
if (isset($_GET['id_pasien_user'])) {
    $id_pasien = mysqli_real_escape_string($db, $_GET['id_pasien_user']);

    // Query untuk menarik data konsultasi sekaligus mengambil NAMA DOKTER
    $query = "SELECT k.id AS id_konsultasi, u_dokter.nama_lengkap AS nama_dokter 
              FROM konsultasi k 
              JOIN dokter d ON k.id_dokter = d.id_dokter 
              JOIN users u_dokter ON d.id_user = u_dokter.id 
              WHERE k.id_pasien = '$id_pasien'";
    
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Error Query: " . mysqli_error($db)]);
        exit;
    }

    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Parameter id_pasien_user tidak dikirim."]);
}
?>

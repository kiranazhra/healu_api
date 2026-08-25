<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "db_healu";

$konek = mysqli_connect($host, $user, $pass, $db_name);

if (!$konek) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

$id_pasien = isset($_POST['id_pasien']) ? mysqli_real_escape_string($konek, $_POST['id_pasien']) : '';
$tanggal_awal = isset($_POST['tanggal_awal']) ? mysqli_real_escape_string($konek, $_POST['tanggal_awal']) : null;
$tanggal_akhir = isset($_POST['tanggal_akhir']) ? mysqli_real_escape_string($konek, $_POST['tanggal_akhir']) : null;

if (empty($id_pasien)) {
    echo json_encode(["status" => "error", "message" => "ID pasien kosong"]);
    exit();
}

$query = "SELECT id, no_invoice, layanan, jumlah_bayar, status_bayar, 
            DATE_FORMAT(tanggal_bayar, '%Y-%m-%d') AS tanggal_bayar
          FROM invoice_pembayaran
          WHERE id_pasien = '$id_pasien'";

if ($tanggal_awal !== null && $tanggal_akhir !== null) {
    $query .= " AND DATE(tanggal_bayar) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
}

$query .= " ORDER BY tanggal_bayar DESC";

$result = mysqli_query($konek, $query);
$data_invoice = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_invoice[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data_invoice]);
} else {
    echo json_encode(["status" => "error", "message" => "Query gagal: " . mysqli_error($konek)]);
}

mysqli_close($konek);
?>
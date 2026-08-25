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

$tanggal_awal = isset($_POST['tanggal_awal']) ? mysqli_real_escape_string($konek, $_POST['tanggal_awal']) : null;
$tanggal_akhir = isset($_POST['tanggal_akhir']) ? mysqli_real_escape_string($konek, $_POST['tanggal_akhir']) : null;

$query = "SELECT inv.id, inv.no_invoice, inv.layanan, inv.jumlah_bayar, inv.status_bayar,
            DATE_FORMAT(inv.tanggal_bayar, '%Y-%m-%d') AS tanggal_bayar,
            inv.id_pasien, u.nama_lengkap AS nama_pasien, u.email AS email_pasien
          FROM invoice_pembayaran inv
          LEFT JOIN users u ON inv.id_pasien = u.id
          WHERE 1=1";

if ($tanggal_awal !== null && $tanggal_akhir !== null) {
    $query .= " AND DATE(inv.tanggal_bayar) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
}

$query .= " ORDER BY inv.tanggal_bayar DESC";

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
<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

// Optional filter: /get_jadwal_dokter.php?id_dokter=1
$id_dokter = isset($_GET['id_dokter']) ? intval($_GET['id_dokter']) : null;

$query = "
    SELECT 
        k.id AS id_konsultasi,
        k.id_dokter,
        d.nama_lengkap AS nama_dokter,
        d.spesialis,
        k.id_pasien,
        u.nama_lengkap AS nama_pasien,
        k.tanggal_jadwal,
        k.waktu_jadwal,
        k.durasi,
        k.status_konsultasi
    FROM konsultasi k
    JOIN dokter d ON k.id_dokter = d.id_dokter
    JOIN users u ON k.id_pasien = u.id
    WHERE k.status_konsultasi NOT IN ('selesai', 'batal')
";

if ($id_dokter !== null) {
    $query .= " AND k.id_dokter = " . $id_dokter;
}

$query .= " ORDER BY k.tanggal_jadwal ASC, k.waktu_jadwal ASC";

$result = mysqli_query($konek, $query);

$jadwal = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jadwal[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $jadwal
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil jadwal dokter: " . mysqli_error($konek)
    ]);
}
?>
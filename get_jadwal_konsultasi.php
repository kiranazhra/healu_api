<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

// Optional filter: /get_jadwal_konsultasi.php?id_pasien=3
$id_pasien = isset($_GET['id_pasien']) ? intval($_GET['id_pasien']) : null;

$query = "
    SELECT 
        k.id AS id_konsultasi,
        k.id_pasien,
        u.nama_lengkap AS nama_pasien,
        k.id_dokter,
        d.nama_lengkap AS nama_dokter,
        d.spesialis,
        k.tanggal_jadwal,
        k.waktu_jadwal,
        k.durasi,
        k.total_harga,
        k.status_konsultasi
    FROM konsultasi k
    JOIN dokter d ON k.id_dokter = d.id_dokter
    JOIN users u ON k.id_pasien = u.id
    WHERE k.status_konsultasi NOT IN ('selesai', 'batal')
";

if ($id_pasien !== null) {
    $query .= " AND k.id_pasien = " . $id_pasien;
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
        "message" => "Gagal mengambil jadwal konsultasi: " . mysqli_error($konek)
    ]);
}
?>
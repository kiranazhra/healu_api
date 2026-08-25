<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");
require 'koneksi.php';

$id_dokter = isset($_GET['id_dokter']) ? intval($_GET['id_dokter']) : null;
$id_pasien = isset($_GET['id_pasien']) ? intval($_GET['id_pasien']) : null;
$tanggal_awal = $_GET['tanggal_awal'] ?? null;
$tanggal_akhir = $_GET['tanggal_akhir'] ?? null;

$query = "SELECT
    r.id_resep, r.nomor_resep, r.id_konsultasi, r.id_pasien, r.id_dokter,
    r.riwayat_alergi, r.status_resep, r.tanggal_dibuat,
    u_pasien.nama_lengkap AS nama_pasien,
    u_dokter.nama_lengkap AS nama_dokter,
    d.spesialis,
    (SELECT COUNT(*) FROM reminder_obat WHERE id_resep = r.id_resep) AS jumlah_obat
FROM resep_obat r
JOIN users u_pasien ON r.id_pasien = u_pasien.id
JOIN users u_dokter ON r.id_dokter = u_dokter.id
LEFT JOIN dokter d ON u_dokter.id = d.id_user
WHERE 1=1";

$params = [];
$types = "";

if ($id_dokter !== null) {
    $query .= " AND r.id_dokter = ?";
    $params[] = $id_dokter;
    $types .= "i";
}
if ($id_pasien !== null) {
    $query .= " AND r.id_pasien = ?";
    $params[] = $id_pasien;
    $types .= "i";
}
if ($tanggal_awal !== null && $tanggal_akhir !== null) {
    $query .= " AND r.tanggal_dibuat BETWEEN ? AND ?";
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
    $types .= "ss";
}

$query .= " ORDER BY r.tanggal_dibuat DESC";

$stmt = $konek->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data]);
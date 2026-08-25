<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

require 'koneksi.php';

$id_dokter = isset($_GET['id_dokter']) ? intval($_GET['id_dokter']) : null;
$id_pasien = isset($_GET['id_pasien']) ? intval($_GET['id_pasien']) : null;
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : null;
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : null;

$query = "SELECT 
    rm.id_rekam,
    rm.nomor_rekam,
    rm.id_konsultasi,
    rm.id_pasien,
    rm.id_dokter,
    rm.subjektif,
    rm.objektif,
    rm.asesmen,
    rm.plan,
    rm.status_rekam,
    rm.tanggal_dibuat,
    u_pasien.nama_lengkap AS nama_pasien,
    u_pasien.nomor_telepon AS telepon_pasien,
    u_dokter.nama_lengkap AS nama_dokter,
    d.spesialis,
    k.tanggal_jadwal,
    k.waktu_jadwal
FROM rekam_medis rm
JOIN users u_pasien ON rm.id_pasien = u_pasien.id
JOIN users u_dokter ON rm.id_dokter = u_dokter.id
LEFT JOIN dokter d ON u_dokter.id = d.id_user
LEFT JOIN konsultasi k ON rm.id_konsultasi = k.id
WHERE 1=1";

$params = [];
$types = "";

if ($id_dokter !== null) {
    $query .= " AND rm.id_dokter = ?";
    $params[] = $id_dokter;
    $types .= "i";
}
if ($id_pasien !== null) {
    $query .= " AND rm.id_pasien = ?";
    $params[] = $id_pasien;
    $types .= "i";
}
if ($tanggal_awal !== null && $tanggal_akhir !== null) {
    $query .= " AND rm.tanggal_dibuat BETWEEN ? AND ?";
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
    $types .= "ss";
}

$query .= " ORDER BY rm.tanggal_dibuat DESC";

$stmt = $konek->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rekam_medis = [];
while ($row = $result->fetch_assoc()) {
    $rekam_medis[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $rekam_medis
]);
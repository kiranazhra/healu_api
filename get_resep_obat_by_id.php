<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");
require 'koneksi.php';

$id_resep = isset($_GET['id_resep']) ? intval($_GET['id_resep']) : 0;

if (!$id_resep) {
    echo json_encode(['success' => false, 'message' => 'ID Resep diperlukan']);
    exit;
}

$query = "SELECT
    r.*,
    u_pasien.nama_lengkap AS nama_pasien,
    u_dokter.nama_lengkap AS nama_dokter,
    d.spesialis, d.no_str
FROM resep_obat r
JOIN users u_pasien ON r.id_pasien = u_pasien.id
JOIN users u_dokter ON r.id_dokter = u_dokter.id
LEFT JOIN dokter d ON u_dokter.id = d.id_user
WHERE r.id_resep = ?";

$stmt = $konek->prepare($query);
$stmt->bind_param("i", $id_resep);
$stmt->execute();
$header = $stmt->get_result()->fetch_assoc();

if (!$header) {
    echo json_encode(['success' => false, 'message' => 'Resep tidak ditemukan']);
    exit;
}

$stmtDetail = $konek->prepare(
    "SELECT id, nama_obat, sediaan, dosis, jumlah, satuan, aturan_pakai, waktu_minum, catatan, status_reminder
     FROM reminder_obat WHERE id_resep = ? ORDER BY id ASC"
);
$stmtDetail->bind_param("i", $id_resep);
$stmtDetail->execute();
$resultDetail = $stmtDetail->get_result();

$items = [];
while ($row = $resultDetail->fetch_assoc()) {
    $items[] = $row;
}

$header['obat'] = $items;

echo json_encode(['success' => true, 'data' => $header]);
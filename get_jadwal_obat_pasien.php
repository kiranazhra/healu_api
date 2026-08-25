<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");
require 'koneksi.php';

$id_pasien = isset($_GET['id_pasien']) ? intval($_GET['id_pasien']) : 0;

if (!$id_pasien) {
    echo json_encode(['success' => false, 'message' => 'id_pasien diperlukan']);
    exit;
}

$stmt = $konek->prepare(
    "SELECT ro.id, ro.nama_obat, ro.dosis, ro.jumlah, ro.satuan, ro.aturan_pakai, ro.waktu_minum, ro.catatan,
            r.nomor_resep, r.status_resep
     FROM reminder_obat ro
     JOIN resep_obat r ON ro.id_resep = r.id_resep
     WHERE ro.id_pasien = ? AND r.status_resep = 'aktif' AND ro.status_reminder = 'aktif'
     ORDER BY ro.waktu_minum ASC"
);
$stmt->bind_param("i", $id_pasien);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data]);
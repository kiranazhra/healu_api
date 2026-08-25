<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

require 'koneksi.php';

$id_rekam = isset($_GET['id_rekam']) ? intval($_GET['id_rekam']) : 0;

if (!$id_rekam) {
    echo json_encode(['success' => false, 'message' => 'ID Rekam diperlukan']);
    exit;
}

$query = "SELECT 
    rm.*,
    u_pasien.nama_lengkap AS nama_pasien,
    u_pasien.nomor_telepon AS telepon_pasien,
    u_pasien.tanggal_lahir,
    u_pasien.jenis_kelamin,
    u_dokter.nama_lengkap AS nama_dokter,
    d.spesialis,
    d.no_str,
    k.tanggal_jadwal,
    k.waktu_jadwal,
    k.durasi
FROM rekam_medis rm
JOIN users u_pasien ON rm.id_pasien = u_pasien.id
JOIN users u_dokter ON rm.id_dokter = u_dokter.id
LEFT JOIN dokter d ON u_dokter.id = d.id_user
LEFT JOIN konsultasi k ON rm.id_konsultasi = k.id
WHERE rm.id_rekam = ?";

$stmt = $konek->prepare($query);
$stmt->bind_param("i", $id_rekam);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Rekam medis tidak ditemukan']);
}
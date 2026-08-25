<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

require 'koneksi.php';

$id_konsultasi = $_POST['id_konsultasi'] ?? null;
$id_pasien = $_POST['id_pasien'] ?? null;
$id_dokter = $_POST['id_dokter'] ?? null;
$subjektif = $_POST['subjektif'] ?? '';
$objektif = $_POST['objektif'] ?? '';
$asesmen = $_POST['asesmen'] ?? '';
$plan = $_POST['plan'] ?? '';
$status_rekam = $_POST['status_rekam'] ?? 'draft';

if (!$id_konsultasi || !$id_pasien || !$id_dokter) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Generate nomor rekam: HU-YYYY-XXXXX
$date = date('Y');
$query_count = "SELECT COUNT(*) as count FROM rekam_medis WHERE YEAR(tanggal_dibuat) = $date";
$result = $konek->query($query_count);
$row = $result->fetch_assoc();
$nomor = str_pad($row['count'] + 1, 5, '0', STR_PAD_LEFT);
$nomor_rekam = "HU-$date-$nomor";

$subjektif_esc = $konek->real_escape_string($subjektif);
$objektif_esc = $konek->real_escape_string($objektif);
$asesmen_esc = $konek->real_escape_string($asesmen);
$plan_esc = $konek->real_escape_string($plan);

$query = "INSERT INTO rekam_medis 
    (id_konsultasi, id_pasien, id_dokter, nomor_rekam, subjektif, objektif, asesmen, plan, status_rekam)
VALUES 
    ($id_konsultasi, $id_pasien, $id_dokter, '$nomor_rekam', '$subjektif_esc', '$objektif_esc', '$asesmen_esc', '$plan_esc', '$status_rekam')";

if ($konek->query($query)) {
    echo json_encode([
        'success' => true,
        'message' => 'Rekam medis berhasil dibuat',
        'nomor_rekam' => $nomor_rekam,
        'id_rekam' => $konek->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . $konek->error]);
}
?>

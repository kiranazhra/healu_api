<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

require 'koneksi.php';

$id_rekam = $_POST['id_rekam'] ?? null;
$subjektif = $_POST['subjektif'] ?? '';
$objektif = $_POST['objektif'] ?? '';
$asesmen = $_POST['asesmen'] ?? '';
$plan = $_POST['plan'] ?? '';
$status_rekam = $_POST['status_rekam'] ?? 'draft';

if (!$id_rekam) {
    echo json_encode(['success' => false, 'message' => 'ID Rekam diperlukan']);
    exit;
}

$subjektif_esc = $konek->real_escape_string($subjektif);
$objektif_esc = $konek->real_escape_string($objektif);
$asesmen_esc = $konek->real_escape_string($asesmen);
$plan_esc = $konek->real_escape_string($plan);

$query = "UPDATE rekam_medis SET 
    subjektif = '$subjektif_esc',
    objektif = '$objektif_esc',
    asesmen = '$asesmen_esc',
    plan = '$plan_esc',
    status_rekam = '$status_rekam',
    updated_at = CURRENT_TIMESTAMP
WHERE id_rekam = $id_rekam";

if ($konek->query($query)) {
    echo json_encode([
        'success' => true,
        'message' => 'Rekam medis berhasil diperbarui'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . $konek->error]);
}
?>

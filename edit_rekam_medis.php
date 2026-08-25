<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

require 'koneksi.php';

$id_rekam     = isset($_POST['id_rekam']) ? intval($_POST['id_rekam']) : 0;
$subjektif    = trim($_POST['subjektif'] ?? '');
$objektif     = trim($_POST['objektif'] ?? '');
$asesmen      = trim($_POST['asesmen'] ?? '');
$plan         = trim($_POST['plan'] ?? '');
$status_rekam = in_array($_POST['status_rekam'] ?? '', ['draft', 'final'])
    ? $_POST['status_rekam']
    : 'final';

if (!$id_rekam) {
    echo json_encode(['success' => false, 'message' => 'id_rekam wajib diisi']);
    exit;
}
if ($subjektif === '' || $objektif === '' || $asesmen === '' || $plan === '') {
    echo json_encode(['success' => false, 'message' => 'Semua field SOAP wajib diisi']);
    exit;
}

// Pastikan datanya ada
$cek = $konek->prepare("SELECT id_rekam FROM rekam_medis WHERE id_rekam = ? LIMIT 1");
$cek->bind_param("i", $id_rekam);
$cek->execute();
if (!$cek->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Rekam medis tidak ditemukan']);
    exit;
}

$stmt = $konek->prepare(
    "UPDATE rekam_medis
     SET subjektif = ?, objektif = ?, asesmen = ?, plan = ?, status_rekam = ?
     WHERE id_rekam = ?"
);
$stmt->bind_param("sssssi", $subjektif, $objektif, $asesmen, $plan, $status_rekam, $id_rekam);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Rekam medis berhasil diperbarui']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui rekam medis: ' . $stmt->error]);
}
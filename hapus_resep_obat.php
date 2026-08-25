<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");
require 'koneksi.php';

$id_resep = isset($_POST['id_resep']) ? intval($_POST['id_resep']) : 0;

if (!$id_resep) {
    echo json_encode(['success' => false, 'message' => 'id_resep wajib diisi']);
    exit;
}

$stmt = $konek->prepare("DELETE FROM resep_obat WHERE id_resep = ?");
$stmt->bind_param("i", $id_resep);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Resep berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . $stmt->error]);
}
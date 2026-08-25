<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$id_pasien = $_POST['id_pasien'] ?? '';

$stmt = $konek->prepare("DELETE FROM users WHERE id = ? AND role = 'pasien'");
$stmt->bind_param("i", $id_pasien);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Pasien berhasil dihapus']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pasien']);
}

$stmt->close();
$konek->close();
?>

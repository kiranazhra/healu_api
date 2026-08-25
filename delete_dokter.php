<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$id_dokter = isset($_POST['id_dokter']) ? $_POST['id_dokter'] : '';

if (empty($id_dokter)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Dokter tidak ditemukan']);
    exit;
}

$get_user = $konek->prepare("SELECT id_user FROM dokter WHERE id_dokter = ?");
$get_user->bind_param("i", $id_dokter);
$get_user->execute();
$result = $get_user->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'Dokter tidak ditemukan']);
    exit;
}

$id_user = $row['id_user'];
$get_user->close();

$delete_dokter = $konek->prepare("DELETE FROM dokter WHERE id_dokter = ?");
$delete_dokter->bind_param("i", $id_dokter);

if (!$delete_dokter->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus dokter']);
    exit;
}

$delete_dokter->close();

$delete_user = $konek->prepare("DELETE FROM users WHERE id = ?");
$delete_user->bind_param("i", $id_user);
$delete_user->execute();
$delete_user->close();

echo json_encode(['status' => 'success', 'message' => 'Dokter berhasil dihapus']);

$konek->close();
?>

<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$id_pasien = $_POST['id_pasien'] ?? '';
$nama_lengkap = $_POST['nama_lengkap'] ?? '';
$email = $_POST['email'] ?? '';
$nomor_telepon = $_POST['nomor_telepon'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$alamat = $_POST['alamat'] ?? '';

$stmt = $konek->prepare("
    UPDATE users 
    SET 
        nama_lengkap = ?,
        email = ?,
        nomor_telepon = ?,
        tanggal_lahir = ?,
        jenis_kelamin = ?,
        alamat = ?
    WHERE id = ? AND role = 'pasien'
");

$stmt->bind_param(
    "ssssssi",
    $nama_lengkap,
    $email,
    $nomor_telepon,
    $tanggal_lahir,
    $jenis_kelamin,
    $alamat,
    $id_pasien
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Pasien berhasil diperbarui']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui pasien']);
}

$stmt->close();
$konek->close();
?>

<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include 'koneksi.php';

$nama_lengkap = isset($_POST['nama_lengkap']) ? $_POST['nama_lengkap'] : '';
$spesialis = isset($_POST['spesialis']) ? $_POST['spesialis'] : '';
$harga_konsultasi = isset($_POST['harga_konsultasi']) ? $_POST['harga_konsultasi'] : 0;
$rating = isset($_POST['rating']) ? $_POST['rating'] : 0;
$no_str = isset($_POST['no_str']) ? $_POST['no_str'] : '';
$nomor_telepon = isset($_POST['nomor_telepon']) ? $_POST['nomor_telepon'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'online';

if (empty($nama_lengkap) || empty($spesialis) || empty($harga_konsultasi)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama, spesialis, dan harga harus diisi']);
    exit;
}

// Kalau email kosong, isi default unik biar nggak bentrok UNIQUE constraint
if (empty($email)) {
    $email = strtolower(str_replace(' ', '', $nama_lengkap)) . time() . '@healu.local';
}

$password = password_hash('dokter123', PASSWORD_DEFAULT);
$insert_user = "INSERT INTO users (nama_lengkap, email, nomor_telepon, password, role) VALUES (?, ?, ?, ?, 'dokter')";

$stmt_user = $konek->prepare($insert_user);

if (!$stmt_user) {
    echo json_encode(['status' => 'error', 'message' => 'Error prepare user: ' . $konek->error]);
    exit;
}

$stmt_user->bind_param("ssss", $nama_lengkap, $email, $nomor_telepon, $password);

if (!$stmt_user->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal membuat akun user: ' . $stmt_user->error]);
    exit;
}

$id_user = $konek->insert_id;
$stmt_user->close();

$insert_dokter = "INSERT INTO dokter (id_user, nama_lengkap, spesialis, no_str, rating, harga_konsultasi, status) 
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $konek->prepare($insert_dokter);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error prepare dokter: ' . $konek->error]);
    exit;
}

$stmt->bind_param(
    "isssdds",
    $id_user,
    $nama_lengkap,
    $spesialis,
    $no_str,
    $rating,
    $harga_konsultasi,
    $status
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Dokter berhasil ditambahkan']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan dokter: ' . $stmt->error]);
}

$stmt->close();
$konek->close();
?>

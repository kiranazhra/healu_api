<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

error_reporting(0); // Mencegah pesan error HTML muncul

$konek = mysqli_connect("localhost", "root", "", "db_healu");

if (!$konek) {
    echo json_encode(["status" => "error", "message" => "Database tidak terhubung!"]);
    exit();
}

// Ambil data dari Flutter
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

// Validasi input wajib
if (empty($email) || empty($password) || empty($nama_lengkap) || empty($role)) {
    echo json_encode(["status" => "error", "message" => "Semua kolom wajib diisi!"]);
    exit();
}

// Validasi role agar hanya pasien/dokter/admin
$allowed_roles = ['pasien', 'dokter', 'admin'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(["status" => "error", "message" => "Role tidak valid!"]);
    exit();
}

// Cek apakah email sudah ada
$cekStmt = mysqli_prepare($konek, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($cekStmt, "s", $email);
mysqli_stmt_execute($cekStmt);
mysqli_stmt_store_result($cekStmt);

if (mysqli_stmt_num_rows($cekStmt) > 0) {
    echo json_encode(["status" => "error", "message" => "Email sudah terdaftar!"]);
    mysqli_stmt_close($cekStmt);
    exit();
}
mysqli_stmt_close($cekStmt);

// Simpan data (password disimpan sebagai teks biasa)
$sqlStmt = mysqli_prepare($konek, "INSERT INTO users (email, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($sqlStmt, "ssss", $email, $password, $nama_lengkap, $role);

if (mysqli_stmt_execute($sqlStmt)) {
    echo json_encode(["status" => "success", "message" => "Pendaftaran berhasil!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . mysqli_stmt_error($sqlStmt)]);
}

mysqli_stmt_close($sqlStmt);
?>
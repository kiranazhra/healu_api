<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = mysqli_connect("localhost", "root", "", "db_healu");

if (!$konek) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

$nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$nomor_telepon = isset($_POST['nomor_telepon']) ? trim($_POST['nomor_telepon']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? trim($_POST['jenis_kelamin']) : '';
$tanggal_lahir = isset($_POST['tanggal_lahir']) ? trim($_POST['tanggal_lahir']) : '';
// Catatan: 'alamat' dikirim dari Flutter tapi tabel users tidak punya kolom untuk itu,
// jadi diterima tapi tidak disimpan. Tambahkan kolom alamat ke tabel users
// kalau nanti data ini perlu benar-benar tersimpan.

if (empty($nama_lengkap) || empty($email) || empty($nomor_telepon) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Semua field wajib diisi!"]);
    exit();
}

// Cek apakah email sudah terdaftar
$stmtCek = mysqli_prepare($konek, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmtCek, "s", $email);
mysqli_stmt_execute($stmtCek);
mysqli_stmt_store_result($stmtCek);

if (mysqli_stmt_num_rows($stmtCek) > 0) {
    echo json_encode(["status" => "error", "message" => "Email sudah terdaftar!"]);
    mysqli_stmt_close($stmtCek);
    exit();
}
mysqli_stmt_close($stmtCek);

$role = 'pasien';

$stmt = mysqli_prepare(
    $konek,
    "INSERT INTO users (nama_lengkap, email, nomor_telepon, tanggal_lahir, jenis_kelamin, password, role, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
);
mysqli_stmt_bind_param(
    $stmt,
    "sssssss",
    $nama_lengkap,
    $email,
    $nomor_telepon,
    $tanggal_lahir,
    $jenis_kelamin,
    $password,
    $role
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => "success",
        "message" => "Pasien berhasil ditambahkan",
        "id_pasien" => mysqli_insert_id($konek)
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menambahkan pasien: " . mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($konek);
?>
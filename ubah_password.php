<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$konek = mysqli_connect("localhost", "root", "", "db_healu");

if (!$konek) {
    echo json_encode(["status" => "error", "message" => "Database tidak terhubung!"]);
    exit();
}

$id_user       = isset($_POST['id_user']) ? trim($_POST['id_user']) : '';
$password_lama = isset($_POST['password_lama']) ? trim($_POST['password_lama']) : '';
$password_baru = isset($_POST['password_baru']) ? trim($_POST['password_baru']) : '';

if (empty($id_user) || empty($password_lama) || empty($password_baru)) {
    echo json_encode(["status" => "error", "message" => "Semua field wajib diisi!"]);
    exit();
}

if (strlen($password_baru) < 6) {
    echo json_encode(["status" => "error", "message" => "Kata sandi baru minimal 6 karakter!"]);
    exit();
}

$stmt = mysqli_prepare($konek, "SELECT password FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "s", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(["status" => "error", "message" => "Akun tidak ditemukan!"]);
    mysqli_stmt_close($stmt);
    exit();
}

$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($password_lama !== $row['password']) {
    echo json_encode(["status" => "error", "message" => "Kata sandi lama salah!"]);
    exit();
}

$update = mysqli_prepare($konek, "UPDATE users SET password = ? WHERE id = ?");
mysqli_stmt_bind_param($update, "ss", $password_baru, $id_user);
mysqli_stmt_execute($update);
mysqli_stmt_close($update);

echo json_encode(["status" => "success", "message" => "Kata sandi berhasil diubah!"]);
?>
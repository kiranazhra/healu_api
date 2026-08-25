<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_healu"; // Sesuai dengan nama database di phpMyAdmin Anda

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi gagal: " . $conn->connect_error]));
}

// Ambil email dari parameter URL
$email = isset($_GET['email']) ? $_GET['email'] : '';

if (empty($email)) {
    echo json_encode(["error" => "Email tidak ditemukan"]);
    exit;
}

// Ambil SEMUA kolom yang dibutuhkan tampilan Data Diri, bukan cuma
// id/nama_lengkap/email. Sebelumnya nomor_telepon, tanggal_lahir, dan
// jenis_kelamin tidak diminta sama sekali dari database, makanya selalu
// tampil kosong di Flutter walau sudah tersimpan di tabel.
$sql = "SELECT id, nama_lengkap, email, nomor_telepon, tanggal_lahir, jenis_kelamin
        FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $row]);
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
}

$stmt->close();
$conn->close();
?>
<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

// Sesuaikan dengan koneksi database kamu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_healu";

$konek = new mysqli($servername, $username, $password, $dbname);

if ($konek->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tangkap data dari Flutter
    $old_email      = $_POST['old_email'] ?? '';
    $new_email      = $_POST['new_email'] ?? '';
    $nama_lengkap   = $_POST['nama_lengkap'] ?? '';
    $nomor_telepon  = trim($_POST['nomor_telepon'] ?? '');
    $tanggal_lahir  = trim($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';

    if ($old_email === '' || $new_email === '') {
        echo json_encode(["status" => "error", "message" => "Email tidak boleh kosong"]);
        $konek->close();
        exit;
    }

    // Kalau kolom tanggal_lahir bertipe DATE, string kosong akan membuat
    // MySQL diam-diam menyimpan '0000-00-00' (kalau strict mode mati).
    // Kirim NULL kalau memang belum diisi, supaya tidak tersimpan aneh
    // dan tetap kebaca "kosong" saat di-GET lagi.
    $tanggal_lahir_value = $tanggal_lahir === '' ? null : $tanggal_lahir;
    $nomor_telepon_value = $nomor_telepon === '' ? null : $nomor_telepon;

    $sql = "UPDATE users SET
                email = ?,
                nama_lengkap = ?,
                nomor_telepon = ?,
                tanggal_lahir = ?,
                jenis_kelamin = ?
            WHERE email = ?";

    $stmt = $konek->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Gagal prepare query: " . $konek->error]);
        $konek->close();
        exit;
    }

    $stmt->bind_param(
        "ssssss",
        $new_email,
        $nama_lengkap,
        $nomor_telepon_value,
        $tanggal_lahir_value,
        $jenis_kelamin,
        $old_email
    );

    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            // Query jalan, tapi tidak ada baris yang match / tidak ada
            // perubahan nilai. Ini kasus umum kenapa "sukses" tapi
            // sebenarnya tidak benar-benar update apa-apa.
            echo json_encode([
                "status" => "success",
                "message" => "Tidak ada perubahan data (email lama mungkin tidak ditemukan atau nilai sama persis)"
            ]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data berhasil diupdate"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal update: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$konek->close();
?>
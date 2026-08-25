<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "db_healu";

$konek = mysqli_connect($host, $user, $pass, $db_name);

if (!$konek) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

// Ambil mood TERBARU dari setiap pasien (1 baris per pasien)
$query = "
    SELECT u.id AS id_pasien, u.nama_lengkap, mm.mood, mm.tanggal_input
    FROM monitoring_mental mm
    INNER JOIN (
        SELECT id_pasien, MAX(id) AS max_id
        FROM monitoring_mental
        GROUP BY id_pasien
    ) latest ON mm.id_pasien = latest.id_pasien AND mm.id = latest.max_id
    INNER JOIN users u ON u.id = mm.id_pasien
    ORDER BY mm.tanggal_input DESC
";

$eksekusi = mysqli_query($konek, $query);

if (!$eksekusi) {
    echo json_encode(["status" => "error", "message" => mysqli_error($konek)]);
    exit();
}

$hasil = [];
while ($row = mysqli_fetch_assoc($eksekusi)) {
    $hasil[] = [
        "id_pasien" => $row['id_pasien'],
        "nama_pasien" => $row['nama_lengkap'],
        "mood" => $row['mood'],
        "tanggal" => $row['tanggal_input'],
    ];
}

echo json_encode(["status" => "success", "data" => $hasil]);
?>

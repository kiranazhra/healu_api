<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "db_healu";

$konek= mysqli_connect($host, $user, $pass, $db_name);

if (!$konek) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

$id_pasien = $_GET['id_pasien'] ?? null;
$periode = $_GET['periode'] ?? 'minggu'; // minggu | bulan | tiga_bulan

if (!$id_pasien) {
    echo json_encode(["status" => "error", "message" => "id_pasien wajib diisi"]);
    exit();
}

// Tentukan rentang tanggal sesuai periode
switch ($periode) {
    case 'bulan':
        $interval = '1 MONTH';
        break;
    case 'tiga_bulan':
        $interval = '3 MONTH';
        break;
    default:
        $interval = '7 DAY';
        break;
}

$id_pasien_safe = mysqli_real_escape_string($konek, $id_pasien);

$query = "
    SELECT mood, tanggal_input
    FROM monitoring_mental
    WHERE id_pasien = '$id_pasien_safe'
    AND tanggal_input >= DATE_SUB(NOW(), INTERVAL $interval)
    ORDER BY tanggal_input DESC
";

$eksekusi = mysqli_query($konek, $query);

if (!$eksekusi) {
    echo json_encode(["status" => "error", "message" => mysqli_error($konek)]);
    exit();
}

$riwayat = [];
while ($row = mysqli_fetch_assoc($eksekusi)) {
    $riwayat[] = [
        "mood" => $row['mood'],
        "tanggal" => $row['tanggal_input'],
    ];
}

echo json_encode(["status" => "success", "data" => $riwayat]);
?>

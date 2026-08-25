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

$email = isset($_GET['email']) ? mysqli_real_escape_string($konek, $_GET['email']) : '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email kosong"]);
    exit();
}

// Ambil semua entri mood 7 hari terakhir, TANPA filter catatan_jurnal
$query = "SELECT id, 
            tanggal_input,
            mood AS nama_mood
          FROM monitoring_mental 
          WHERE email = '$email' 
          AND tanggal_input >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          ORDER BY tanggal_input DESC";

$result = mysqli_query($konek, $query);
$data_grafik = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_grafik[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data_grafik]);
} else {
    echo json_encode(["status" => "error", "message" => "Query gagal"]);
}

mysqli_close($konek);
?>
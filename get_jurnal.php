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

$query = "SELECT id, 
            tanggal_input,
            CASE DAYNAME(tanggal_input)
                WHEN 'Monday' THEN 'Senin'
                WHEN 'Tuesday' THEN 'Selasa'
                WHEN 'Wednesday' THEN 'Rabu'
                WHEN 'Thursday' THEN 'Kamis'
                WHEN 'Friday' THEN 'Jumat'
                WHEN 'Saturday' THEN 'Sabtu'
                WHEN 'Sunday' THEN 'Minggu'
                ELSE 'Hari Ini'
            END AS hari, 
            mood AS nama_mood, 
            catatan_jurnal 
          FROM monitoring_mental 
          WHERE email = '$email' 
          AND catatan_jurnal IS NOT NULL 
          AND TRIM(catatan_jurnal) != ''
          ORDER BY id DESC"; 

$result = mysqli_query($konek, $query);
$data_jurnal = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_jurnal[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data_jurnal]);
} else {
    echo json_encode(["status" => "error", "message" => "Query gagal"]);
}

mysqli_close($konek);
?>
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

$id_pasien = $_GET['id_pasien'] ?? 1;

// 2. Query disempurnakan menggunakan DESC agar 7 data yang diambil adalah 7 DATA TERBARU
$query = "SELECT mood, tanggal_input FROM monitoring_mental WHERE id_pasien = '$id_pasien' ORDER BY id DESC LIMIT 7";
$eksekusi = mysqli_query($konek, $query);

$riwayat = [];
while ($row = mysqli_fetch_assoc($eksekusi)) {
    // Mengonversi string nama mood menjadi nilai angka koordinat grafik Y (1-5)
    $nilai_mood = 3; 
    if ($row['mood'] == 'Sangat Baik') $nilai_mood = 5;
    if ($row['mood'] == 'Baik') $nilai_mood = 4;
    if ($row['mood'] == 'Biasa Saja') $nilai_mood = 3;
    if ($row['mood'] == 'Buruk') $nilai_mood = 2;
    if ($row['mood'] == 'Sangat Buruk') $nilai_mood = 1;

    // Mengonversi format tanggal menjadi nama hari pendek (Sen, Sel, Rab...)
    $nama_hari = date('D', strtotime($row['tanggal_input']));
    $hari_indo = ['Mon'=>'Sen', 'Tue'=>'Sel', 'Wed'=>'Rab', 'Thu'=>'Kam', 'Fri'=>'Jum', 'Sat'=>'Sab', 'Sun'=>'Min'];
    
    $riwayat[] = [
        "hari" => $hari_indo[$nama_hari] ?? $nama_hari,
        "nilai" => $nilai_mood,
        "nama_mood" => $row['mood']
    ];
}

// 3. Membalik urutan array hasil DESC tadi agar saat tampil di grafik runtut dari kiri (hari lama) ke kanan (hari baru)
$riwayat = array_reverse($riwayat);

echo json_encode(["status" => "success", "data" => $riwayat]);
?>

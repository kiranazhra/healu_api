<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");;

// 2. TANGANI REQUEST PREFLIGHT (OPTIONS) DARI BROWSER
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

// 3. AMBIL DATA JSON YANG DIKIRIM DARI FLUTTER
// Karena Flutter mengirim data via raw body (bukan Form-Data), kita gunakan php://input
$data = json_decode(file_get_contents("php://input"));

// 4. VALIDASI DAN SIMPAN KE DATABASE
if (isset($data->id_konsultasi) && isset($data->id_pengirim) && isset($data->pesan)) {
    
    // Amankan data dari injeksi SQL
    $id_konsultasi = mysqli_real_escape_string($konek, $data->id_konsultasi);
    $id_pengirim = mysqli_real_escape_string($konek, $data->id_pengirim);
    $pesan = mysqli_real_escape_string($konek, $data->pesan);
    
    // Query untuk menyimpan chat
    $query = "INSERT INTO isi_chat (id_konsultasi, id_pengirim, pesan) 
              VALUES ('$id_konsultasi', '$id_pengirim', '$pesan')";
    
    if (mysqli_query($konek, $query)) {
        echo json_encode([
            "status" => "success", 
            "message" => "Pesan berhasil disimpan"
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Gagal menyimpan pesan: " . mysqli_error($konek)
        ]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Data yang dikirim tidak lengkap"
    ]);
}
?>

<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$query = "
    SELECT 
        d.id_dokter, 
        d.id_user, 
        d.nama_lengkap, 
        d.spesialis, 
        d.no_str,
        d.rating, 
        d.harga_konsultasi,
        d.status,
        u.email 
    FROM dokter d
    JOIN users u ON d.id_user = u.id
    WHERE u.role = 'dokter'
";

$result = mysqli_query($konek, $query);

$dokters = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dokters[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "data" => $dokters
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil data dokter: " . mysqli_error($konek)
    ]);
}
?>
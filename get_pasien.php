<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$query = "
    SELECT 
        u.id, 
        u.nama_lengkap, 
        u.email, 
        u.nomor_telepon, 
        u.tanggal_lahir, 
        u.jenis_kelamin, 
        u.created_at,
        d.nama_lengkap AS nama_dokter,
        k.status_konsultasi
    FROM users u
    LEFT JOIN konsultasi k 
        ON k.id = (
            SELECT k2.id FROM konsultasi k2 
            WHERE k2.id_pasien = u.id 
            ORDER BY k2.created_at DESC 
            LIMIT 1
        )
    LEFT JOIN dokter d ON d.id_dokter = k.id_dokter
    WHERE u.role = 'pasien'
    ORDER BY u.created_at DESC
";

$eksekusi = mysqli_query($konek, $query);

if ($eksekusi) {
    $daftar_pasien = [];
    while ($row = mysqli_fetch_assoc($eksekusi)) {
        $daftar_pasien[] = $row;
    }
    echo json_encode([
        "status" => "success",
        "data" => $daftar_pasien,
        "total" => count($daftar_pasien)
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => mysqli_error($konek)
    ]);
}

mysqli_close($konek);
?>

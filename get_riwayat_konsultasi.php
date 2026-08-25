<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$id_pasien = $_GET['id_pasien_user'] ?? '';

if(empty($id_pasien)) {
    echo json_encode(["status" => "error", "message" => "ID Pasien tidak ditemukan"]);
    exit;
}

// Query dengan JOIN yang benar (dokter.id_dokter, bukan dokter.id)
$query = "SELECT 
            k.id, 
            k.id_pasien,
            k.id_dokter,
            k.tanggal_jadwal, 
            k.waktu_jadwal, 
            k.durasi, 
            k.total_harga, 
            k.status_konsultasi, 
            k.alasan_batal,
            k.keluhan_utama,
            k.alergi_obat,
            k.diagnosa,
            k.saran_terapi,
            k.pantangan,
            k.resep_obat,
            k.tindak_lanjut,
            k.waktu_mulai,
            k.waktu_selesai,
            k.created_at,
            d.nama_lengkap AS nama_dokter,
            d.nomor_telepon AS hp_dokter,
            d.email AS email_dokter,
            dok.spesialis,
            dok.rating,
            dok.harga_konsultasi,
            p.nama_lengkap AS nama_pasien,
            p.nomor_telepon AS hp_pasien,
            p.email AS email_pasien
          FROM konsultasi k 
          LEFT JOIN dokter dok ON k.id_dokter = dok.id_dokter
          LEFT JOIN users d ON dok.id_user = d.id
          LEFT JOIN users p ON k.id_pasien = p.id
          WHERE k.id_pasien = ? 
          ORDER BY k.created_at DESC";

$stmt = mysqli_prepare($konek, $query); 
mysqli_stmt_bind_param($stmt, "s", $id_pasien); 
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$riwayat = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Penanganan NULL untuk field jadwal
    if (empty($row['tanggal_jadwal'])) $row['tanggal_jadwal'] = "Belum diatur";
    if (empty($row['waktu_jadwal'])) $row['waktu_jadwal'] = "Belum diatur";
    if (empty($row['durasi'])) $row['durasi'] = "30";
    if (empty($row['total_harga'])) $row['total_harga'] = "0";
    if (empty($row['alasan_batal'])) $row['alasan_batal'] = "";

    // Penanganan NULL untuk field medis
    if (empty($row['keluhan_utama'])) $row['keluhan_utama'] = "-";
    if (empty($row['alergi_obat'])) $row['alergi_obat'] = "Tidak ada riwayat alergi";
    if (empty($row['diagnosa'])) $row['diagnosa'] = "Belum ada diagnosa";
    if (empty($row['saran_terapi'])) $row['saran_terapi'] = "-";
    if (empty($row['pantangan'])) $row['pantangan'] = "-";
    if (empty($row['resep_obat'])) $row['resep_obat'] = "-";
    if (empty($row['tindak_lanjut'])) $row['tindak_lanjut'] = "-";
    
    // Penanganan NULL untuk field dokter
    if (empty($row['nama_dokter'])) $row['nama_dokter'] = "Dokter";
    if (empty($row['spesialis'])) $row['spesialis'] = "-";
    if (empty($row['rating'])) $row['rating'] = "0";
    if (empty($row['harga_konsultasi'])) $row['harga_konsultasi'] = "0";

    $riwayat[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $riwayat
]);
?>

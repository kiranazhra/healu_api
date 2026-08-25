<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = new mysqli("localhost", "root", "", "db_healu");

if ($konek->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
}

$id = $_GET['id_konsultasi'] ?? '';

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID konsultasi tidak ditemukan']);
    exit;
}

$id_esc = $konek->real_escape_string($id);

// id_dokter mengacu ke dokter.id_dokter, id_pasien mengacu ke users.id
$sql = "SELECT 
            k.id,
            k.id_pasien,
            k.id_dokter,
            k.tanggal_jadwal,
            k.waktu_jadwal,
            k.durasi,
            k.total_harga,
            k.status_konsultasi,
            k.waktu_mulai,
            k.waktu_selesai,
            k.keluhan_utama,
            k.alergi_obat,
            k.diagnosa,
            k.saran_terapi,
            k.pantangan,
            k.resep_obat,
            k.tindak_lanjut,
            k.created_at,
            d.nama_lengkap  AS nama_dokter,
            d.nomor_telepon AS hp_dokter,
            dok.spesialis,
            dok.harga_konsultasi,
            p.nama_lengkap  AS nama_pasien,
            p.nomor_telepon AS hp_pasien,
            p.email         AS email_pasien,
            TIMESTAMPDIFF(MINUTE, k.waktu_mulai, k.waktu_selesai) AS durasi_menit
        FROM konsultasi k
        LEFT JOIN dokter dok ON k.id_dokter = dok.id_dokter
        LEFT JOIN users d ON dok.id_user = d.id
        LEFT JOIN users p ON k.id_pasien = p.id
        WHERE k.id = '$id_esc'";

$result = $konek->query($sql);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $konek->error]);
    exit;
}

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'data'   => $data
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data konsultasi tidak ditemukan'
    ]);
}

$konek->close();
?>

<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php'; 

$db = $konek;

if (isset($_GET['id_dokter_user'])) {
    $id_user = mysqli_real_escape_string($db, $_GET['id_dokter_user']);

    // Cari id_dokter dari tabel dokter berdasarkan id_user
    $query_dokter = "SELECT id_dokter FROM dokter WHERE id_user = '$id_user'";
    $res_dokter = mysqli_query($db, $query_dokter);
    
    if (mysqli_num_rows($res_dokter) > 0) {
        $data_dokter = mysqli_fetch_assoc($res_dokter);
        $id_dokter = $data_dokter['id_dokter'];

        // Query lengkap dengan semua field yang diperlukan
        $query = "SELECT 
                    k.id,
                    k.id_pasien,
                    k.id_dokter,
                    k.tanggal_jadwal,
                    k.waktu_jadwal,
                    k.durasi,
                    k.total_harga,
                    k.status_konsultasi,
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
                    dok.spesialis,
                    dok.rating,
                    p.nama_lengkap AS nama_pasien,
                    p.nomor_telepon AS hp_pasien,
                    p.email AS email_pasien,
                    p.tanggal_lahir AS tgl_lahir_pasien,
                    p.jenis_kelamin AS gender_pasien
                FROM konsultasi k
                LEFT JOIN dokter dok ON k.id_dokter = dok.id_dokter
                LEFT JOIN users d ON dok.id_user = d.id
                LEFT JOIN users p ON k.id_pasien = p.id
                WHERE k.id_dokter = '$id_dokter'
                ORDER BY 
                    CASE 
                        WHEN k.status_konsultasi = 'berlangsung' THEN 1
                        WHEN k.status_konsultasi = 'menunggu_konsultasi' THEN 2
                        WHEN k.status_konsultasi = 'menunggu_pembayaran' THEN 3
                        ELSE 4
                    END,
                    k.tanggal_jadwal ASC,
                    k.waktu_jadwal ASC";
        
        $result = mysqli_query($db, $query);
        
        if (!$result) {
            echo json_encode(["status" => "error", "message" => "Query error: " . mysqli_error($db)]);
            exit;
        }
        
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        if (!empty($data)) {
            echo json_encode(["status" => "success", "data" => $data]);
        } else {
            echo json_encode(["status" => "success", "data" => []]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Dokter tidak ditemukan"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "ID dokter user tidak ditemukan"]);
}
?>

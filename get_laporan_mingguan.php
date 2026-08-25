<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = mysqli_connect("localhost", "root", "", "db_healu");

if (!$konek) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

$id_pasien = isset($_POST['id_pasien']) ? $_POST['id_pasien'] : '';
$tanggal_awal = isset($_POST['tanggal_awal']) ? $_POST['tanggal_awal'] : null;
$tanggal_akhir = isset($_POST['tanggal_akhir']) ? $_POST['tanggal_akhir'] : null;

if (empty($id_pasien)) {
    echo json_encode(["status" => "error", "message" => "ID Pasien tidak boleh kosong"]);
    exit();
}

$pakaiRentangCustom = ($tanggal_awal !== null && $tanggal_akhir !== null);

// Label periode untuk ditampilkan di UI/PDF
$periode = $pakaiRentangCustom
    ? "$tanggal_awal s/d $tanggal_akhir"
    : "7 Hari Terakhir";

// 1. AMBIL DATA REKAP MOOD
if ($pakaiRentangCustom) {
    $stmt = $konek->prepare("
        SELECT mood, COUNT(*) as jumlah 
        FROM monitoring_mental 
        WHERE id_pasien = ? AND DATE(tanggal_input) BETWEEN ? AND ?
        GROUP BY mood
    ");
    $stmt->bind_param("iss", $id_pasien, $tanggal_awal, $tanggal_akhir);
} else {
    $stmt = $konek->prepare("
        SELECT mood, COUNT(*) as jumlah 
        FROM monitoring_mental 
        WHERE id_pasien = ? AND tanggal_input >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY mood
    ");
    $stmt->bind_param("i", $id_pasien);
}
$stmt->execute();
$result_mood = $stmt->get_result();
$mood_data = [];
while ($row = $result_mood->fetch_assoc()) {
    $mood_data[] = $row;
}
$stmt->close();

// 2. AMBIL DATA JURNAL
if ($pakaiRentangCustom) {
    $stmt = $konek->prepare("
        SELECT catatan_jurnal as catatan, tanggal_input as tanggal 
        FROM monitoring_mental 
        WHERE id_pasien = ? 
        AND catatan_jurnal IS NOT NULL 
        AND catatan_jurnal != '' 
        AND DATE(tanggal_input) BETWEEN ? AND ?
        ORDER BY tanggal_input DESC
    ");
    $stmt->bind_param("iss", $id_pasien, $tanggal_awal, $tanggal_akhir);
} else {
    $stmt = $konek->prepare("
        SELECT catatan_jurnal as catatan, tanggal_input as tanggal 
        FROM monitoring_mental 
        WHERE id_pasien = ? 
        AND catatan_jurnal IS NOT NULL 
        AND catatan_jurnal != '' 
        AND tanggal_input >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY tanggal_input DESC
    ");
    $stmt->bind_param("i", $id_pasien);
}
$stmt->execute();
$result_jurnal = $stmt->get_result();
$jurnal_data = [];
while ($row = $result_jurnal->fetch_assoc()) {
    $jurnal_data[] = $row;
}
$stmt->close();

// 3. AMBIL DATA DAFTAR OBAT AKTIF (tidak ada kolom tanggal, jadi tidak difilter periode)
$stmt = $konek->prepare("
    SELECT nama_obat, dosis, waktu_minum, catatan 
    FROM reminder_obat 
    WHERE id_pasien = ?
");
$stmt->bind_param("i", $id_pasien);
$stmt->execute();
$result_obat = $stmt->get_result();
$obat_data = [];
while ($row = $result_obat->fetch_assoc()) {
    $row['status'] = "Aktif (Setiap Pukul " . $row['waktu_minum'] . ")";
    $row['jumlah'] = $row['dosis'];
    $obat_data[] = $row;
}
$stmt->close();

echo json_encode([
    "status" => "success",
    "id_pasien" => $id_pasien,
    "periode" => $periode,
    "data" => [
        "mood" => $mood_data,
        "jurnal" => $jurnal_data,
        "obat" => $obat_data
    ]
]);

mysqli_close($konek);
?>
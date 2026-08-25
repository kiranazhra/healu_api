<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");
require 'koneksi.php';

$id_konsultasi  = (isset($_POST['id_konsultasi']) && $_POST['id_konsultasi'] !== '')
    ? intval($_POST['id_konsultasi']) : null;
$id_pasien      = isset($_POST['id_pasien']) ? intval($_POST['id_pasien']) : 0;
$id_dokter      = isset($_POST['id_dokter']) ? intval($_POST['id_dokter']) : 0;
$riwayat_alergi = trim($_POST['riwayat_alergi'] ?? '');
$status_resep   = in_array($_POST['status_resep'] ?? '', ['draft', 'aktif', 'selesai', 'dibatalkan'])
    ? $_POST['status_resep'] : 'aktif';
$items          = json_decode($_POST['items'] ?? '[]', true);

if (!$id_pasien || !$id_dokter) {
    echo json_encode(['success' => false, 'message' => 'id_pasien dan id_dokter wajib diisi']);
    exit;
}
if (!is_array($items) || count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'Minimal 1 obat harus diisi']);
    exit;
}

$konek->begin_transaction();
try {
    $tahun = date('Y');
    $resCount = $konek->query("SELECT COUNT(*) as count FROM resep_obat WHERE YEAR(tanggal_dibuat) = $tahun");
    $rowCount = $resCount->fetch_assoc();
    $nomorUrut = str_pad($rowCount['count'] + 1, 5, '0', STR_PAD_LEFT);
    $nomor_resep = "RX-HEALU-$tahun$nomorUrut";

    $stmt = $konek->prepare(
        "INSERT INTO resep_obat (nomor_resep, id_konsultasi, id_pasien, id_dokter, riwayat_alergi, status_resep)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("siiiss", $nomor_resep, $id_konsultasi, $id_pasien, $id_dokter, $riwayat_alergi, $status_resep);
    $stmt->execute();
    $id_resep = $konek->insert_id;

    $stmtItem = $konek->prepare(
        "INSERT INTO reminder_obat
            (id_resep, id_pasien, nama_obat, sediaan, dosis, jumlah, satuan, aturan_pakai, waktu_minum, catatan, status_reminder)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($items as $it) {
        $nama_obat       = trim($it['nama_obat'] ?? '');
        $sediaan         = trim($it['sediaan'] ?? 'Tablet');
        $dosis           = trim($it['dosis'] ?? '');
        $jumlah          = intval($it['jumlah'] ?? 1);
        $satuan          = trim($it['satuan'] ?? 'Tablet');
        $aturan_pakai    = trim($it['aturan_pakai'] ?? '');
        $waktu_minum     = !empty($it['waktu_minum']) ? $it['waktu_minum'] : null;
        $catatan         = trim($it['catatan'] ?? '');
        $status_reminder = in_array($it['status_reminder'] ?? '', ['aktif', 'nonaktif'])
            ? $it['status_reminder'] : 'aktif';

        if ($nama_obat === '' || $aturan_pakai === '') continue;

        $stmtItem->bind_param(
            "iisssisssss",
            $id_resep, $id_pasien, $nama_obat, $sediaan, $dosis, $jumlah, $satuan, $aturan_pakai, $waktu_minum, $catatan, $status_reminder
        );
        $stmtItem->execute();
    }

    $konek->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Resep berhasil dibuat',
        'id_resep' => $id_resep,
        'nomor_resep' => $nomor_resep,
    ]);
} catch (Exception $e) {
    $konek->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
}
<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");
require 'koneksi.php';

$id_resep       = isset($_POST['id_resep']) ? intval($_POST['id_resep']) : 0;
$riwayat_alergi = trim($_POST['riwayat_alergi'] ?? '');
$status_resep   = in_array($_POST['status_resep'] ?? '', ['draft', 'aktif', 'selesai', 'dibatalkan'])
    ? $_POST['status_resep'] : 'aktif';
$items          = json_decode($_POST['items'] ?? '[]', true);

if (!$id_resep) {
    echo json_encode(['success' => false, 'message' => 'id_resep wajib diisi']);
    exit;
}
if (!is_array($items) || count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'Minimal 1 obat harus diisi']);
    exit;
}

$cek = $konek->prepare("SELECT id_pasien FROM resep_obat WHERE id_resep = ? LIMIT 1");
$cek->bind_param("i", $id_resep);
$cek->execute();
$existing = $cek->get_result()->fetch_assoc();
if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'Resep tidak ditemukan']);
    exit;
}
$id_pasien = intval($existing['id_pasien']);

$konek->begin_transaction();
try {
    $stmt = $konek->prepare(
        "UPDATE resep_obat SET riwayat_alergi = ?, status_resep = ? WHERE id_resep = ?"
    );
    $stmt->bind_param("ssi", $riwayat_alergi, $status_resep, $id_resep);
    $stmt->execute();

    $stmtDel = $konek->prepare("DELETE FROM reminder_obat WHERE id_resep = ?");
    $stmtDel->bind_param("i", $id_resep);
    $stmtDel->execute();

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
    echo json_encode(['success' => true, 'message' => 'Resep berhasil diperbarui']);
} catch (Exception $e) {
    $konek->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
}
<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';
$db = $konek;

$id_pasien = $_POST['id_pasien'] ?? '';
$id_dokter = $_POST['id_dokter'] ?? '';
$tanggal_jadwal = $_POST['tanggal_jadwal'] ?? '';
$waktu_jadwal = $_POST['waktu_jadwal'] ?? '';
$durasi = $_POST['durasi'] ?? '30';
$total_harga = $_POST['total_harga'] ?? '0';

if (empty($id_pasien) || empty($id_dokter) || empty($tanggal_jadwal) || empty($waktu_jadwal)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

// Cek apakah sudah ada sesi aktif untuk pasien ini
$stmtCek = $db->prepare(
    "SELECT id FROM konsultasi 
     WHERE id_pasien = ? 
     AND status_konsultasi IN ('menunggu_pembayaran', 'menunggu_konsultasi', 'berlangsung')
     LIMIT 1"
);
$stmtCek->bind_param("s", $id_pasien);
$stmtCek->execute();
$resultCek = $stmtCek->get_result();

if ($resultCek->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Anda sudah memiliki sesi konsultasi yang aktif. Selesaikan terlebih dahulu.'
    ]);
    $stmtCek->close();
    exit;
}
$stmtCek->close();

// Transaksi: insert konsultasi + insert invoice (langsung lunas, karena endpoint ini
// dipanggil tepat saat pasien menekan "Bayar Sekarang")
$db->begin_transaction();

try {
    // 1. Insert konsultasi dengan status menunggu_konsultasi (karena pembayaran dianggap selesai di sini)
    $stmtKonsultasi = $db->prepare(
        "INSERT INTO konsultasi 
            (id_pasien, id_dokter, tanggal_jadwal, waktu_jadwal, durasi, total_harga, status_konsultasi, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 'menunggu_konsultasi', NOW())"
    );
    $stmtKonsultasi->bind_param(
        "ssssid",
        $id_pasien, $id_dokter, $tanggal_jadwal, $waktu_jadwal, $durasi, $total_harga
    );

    if (!$stmtKonsultasi->execute()) {
        throw new Exception("Insert konsultasi gagal: " . $stmtKonsultasi->error);
    }

    $idKonsultasi = $db->insert_id;

    // 2. Insert invoice, langsung berstatus lunas (karena tidak ada payment gateway sungguhan)
    $no_invoice = "INV-" . date("Ymd") . "-" . $idKonsultasi;
    $layanan = "Konsultasi Chat";

    $stmtInvoice = $db->prepare(
        "INSERT INTO invoice_pembayaran 
            (id_pasien, id_konsultasi, no_invoice, layanan, jumlah_bayar, status_bayar, tanggal_bayar, created_at)
         VALUES (?, ?, ?, ?, ?, 'lunas', NOW(), NOW())"
    );
    $stmtInvoice->bind_param(
        "sissd",
        $id_pasien, $idKonsultasi, $no_invoice, $layanan, $total_harga
    );

    if (!$stmtInvoice->execute()) {
        throw new Exception("Insert invoice gagal: " . $stmtInvoice->error);
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Pembayaran berhasil, konsultasi siap dimulai.',
        'id_konsultasi' => $idKonsultasi,
        'no_invoice' => $no_invoice
    ]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memproses pendaftaran: ' . $e->getMessage()
    ]);
}

$db->close();
?>
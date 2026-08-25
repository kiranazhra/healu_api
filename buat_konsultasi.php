<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = new mysqli("localhost", "root", "", "db_healu");

if ($konek->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

// Ambil data dari Flutter
$id_pasien = $_POST['id_pasien'] ?? '';
$id_dokter = $_POST['id_dokter'] ?? '';
$jenis_sesi = $_POST['jenis_sesi'] ?? '';
$tanggal_konsultasi = $_POST['tanggal_konsultasi'] ?? '';
$jam_konsultasi = $_POST['jam_konsultasi'] ?? '';
$catatan = $_POST['catatan'] ?? '';

if (empty($id_pasien) || empty($id_dokter) || empty($tanggal_konsultasi) || empty($jam_konsultasi)) {
    echo json_encode(["status" => "error", "message" => "Data konsultasi tidak lengkap"]);
    exit();
}

$durasi = 30;

$stmtDokter = $konek->prepare("SELECT harga_konsultasi FROM dokter WHERE id_dokter = ?");
$stmtDokter->bind_param("s", $id_dokter);
$stmtDokter->execute();
$resDokter = $stmtDokter->get_result();

if ($resDokter->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Dokter tidak ditemukan"]);
    exit();
}

$dataDokter = $resDokter->fetch_assoc();
$biaya = $dataDokter['harga_konsultasi'] ?? 0;
$stmtDokter->close();

$konek->begin_transaction();

try {
    $stmtKonsultasi = $konek->prepare(
        "INSERT INTO konsultasi 
            (id_pasien, id_dokter, tanggal_jadwal, waktu_jadwal, durasi, total_harga, status_konsultasi, keluhan_utama, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 'menunggu_pembayaran', ?, NOW())"
    );
    $stmtKonsultasi->bind_param(
        "ssssids",
        $id_pasien,
        $id_dokter,
        $tanggal_konsultasi,
        $jam_konsultasi,
        $durasi,
        $biaya,
        $catatan
    );

    // Cek eksplisit: kalau execute gagal, lempar exception manual
    if (!$stmtKonsultasi->execute()) {
        throw new Exception("Insert konsultasi gagal: " . $stmtKonsultasi->error);
    }

    $id_konsultasi_baru = $konek->insert_id;

    $no_invoice = "INV-" . date("Ymd") . "-" . $id_konsultasi_baru;
    $layanan = "Konsultasi " . $jenis_sesi;

    $stmtInvoice = $konek->prepare(
        "INSERT INTO invoice_pembayaran 
            (id_pasien, id_konsultasi, no_invoice, layanan, jumlah_bayar, status_bayar, tanggal_bayar, created_at)
         VALUES (?, ?, ?, ?, ?, 'belum_lunas', NULL, NOW())"
    );
    $stmtInvoice->bind_param(
        "sissd",
        $id_pasien,
        $id_konsultasi_baru,
        $no_invoice,
        $layanan,
        $biaya
    );

    // Cek eksplisit juga di sini
    if (!$stmtInvoice->execute()) {
        throw new Exception("Insert invoice gagal: " . $stmtInvoice->error);
    }

    $konek->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Konsultasi dan invoice berhasil dibuat",
        "data" => [
            "id_konsultasi" => $id_konsultasi_baru,
            "no_invoice" => $no_invoice
        ]
    ]);
} catch (Exception $e) {
    $konek->rollback();
    echo json_encode(["status" => "error", "message" => "Gagal membuat konsultasi: " . $e->getMessage()]);
}

$konek->close();
?>
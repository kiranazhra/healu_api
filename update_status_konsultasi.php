<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = new mysqli("localhost", "root", "", "db_healu");

$id          = $_POST['id_konsultasi'] ?? '';
$status_baru = $_POST['status']        ?? '';

if (empty($id) || empty($status_baru)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

// Catat waktu otomatis
$waktu_field = "";
if ($status_baru === 'berlangsung') {
    $waktu_field = ", waktu_mulai = NOW()";
} elseif ($status_baru === 'selesai') {
    $waktu_field = ", waktu_selesai = NOW()";
}

// Kolom medis (opsional saat selesai)
$medis_field = "";
if (isset($_POST['diagnosa'])) {
    $kel  = $konek->real_escape_string($_POST['keluhan_utama']  ?? '');
    $alg  = $konek->real_escape_string($_POST['alergi_obat']    ?? '');
    $diag = $konek->real_escape_string($_POST['diagnosa']       ?? '');
    $sar  = $konek->real_escape_string($_POST['saran_terapi']   ?? '');
    $pan  = $konek->real_escape_string($_POST['pantangan']      ?? '');
    $res  = $konek->real_escape_string($_POST['resep_obat']     ?? '');
    $tl   = $konek->real_escape_string($_POST['tindak_lanjut']  ?? '');

    $medis_field = ", keluhan_utama='$kel', alergi_obat='$alg', diagnosa='$diag',
                     saran_terapi='$sar', pantangan='$pan', resep_obat='$res',
                     tindak_lanjut='$tl'";
}

$st  = $konek->real_escape_string($status_baru);
$sql = "UPDATE konsultasi 
        SET status_konsultasi='$st' $waktu_field $medis_field 
        WHERE id='$id'";

if ($konek->query($sql)) {
    echo json_encode(['status' => 'success', 'message' => "Status berhasil diubah menjadi: $st"]);
} else {
    echo json_encode(['status' => 'error', 'message' => $konek->error]);
}
$konek->close();
?>

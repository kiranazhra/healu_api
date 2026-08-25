<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$id_dokter = isset($_POST['id_dokter']) ? $_POST['id_dokter'] : '';
$nama_lengkap = isset($_POST['nama_lengkap']) ? $_POST['nama_lengkap'] : '';
$spesialis = isset($_POST['spesialis']) ? $_POST['spesialis'] : '';
$harga_konsultasi = isset($_POST['harga_konsultasi']) ? $_POST['harga_konsultasi'] : 0;
$rating = isset($_POST['rating']) ? $_POST['rating'] : 0;
$no_str = isset($_POST['no_str']) ? $_POST['no_str'] : '';
$nomor_telepon = isset($_POST['nomor_telepon']) ? $_POST['nomor_telepon'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'online';

if (empty($id_dokter)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Dokter tidak ditemukan']);
    exit;
}

if (empty($nama_lengkap) || empty($spesialis) || empty($harga_konsultasi)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama, spesialis, dan harga harus diisi']);
    exit;
}

$query = "UPDATE dokter SET 
    nama_lengkap = ?, 
    spesialis = ?, 
    no_str = ?, 
    rating = ?, 
    harga_konsultasi = ?, 
    status = ? 
WHERE id_dokter = ?";

$stmt = $konek->prepare($query);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error prepare: ' . $konek->error]);
    exit;
}

$stmt->bind_param(
    "sssidsi",
    $nama_lengkap,
    $spesialis,
    $no_str,
    $rating,
    $harga_konsultasi,
    $status,
    $id_dokter
);

if ($stmt->execute()) {
    if (!empty($nomor_telepon) || !empty($email)) {
        $update_user = "UPDATE users SET ";
        $params = [];
        $types = "";
        
        if (!empty($nomor_telepon)) {
            $update_user .= "nomor_telepon = ?";
            $params[] = $nomor_telepon;
            $types .= "s";
        }
        
        if (!empty($email)) {
            if (!empty($nomor_telepon)) {
                $update_user .= ", ";
            }
            $update_user .= "email = ?";
            $params[] = $email;
            $types .= "s";
        }
        
        $get_user = $konek->prepare("SELECT id_user FROM dokter WHERE id_dokter = ?");
        $get_user->bind_param("i", $id_dokter);
        $get_user->execute();
        $result = $get_user->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            $id_user = $row['id_user'];
            $update_user .= " WHERE id = ?";
            $params[] = $id_user;
            $types .= "i";
            
            $stmt_user = $konek->prepare($update_user);
            $stmt_user->bind_param($types, ...$params);
            $stmt_user->execute();
            $stmt_user->close();
        }
        
        $get_user->close();
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Dokter berhasil diperbarui']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui dokter: ' . $stmt->error]);
}

$stmt->close();
$konek->close();
?>

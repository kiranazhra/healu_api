<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? mysqli_real_escape_string($konek, $_POST['email']) : '';
    $mood = isset($_POST['mood']) ? mysqli_real_escape_string($konek, $_POST['mood']) : '';
    $catatan = isset($_POST['catatan_jurnal']) ? mysqli_real_escape_string($konek, $_POST['catatan_jurnal']) : '';
    $tanggal_input = date('Y-m-d');

    if (empty($email)) {
        echo json_encode(["status" => "error", "message" => "Email tidak terdeteksi!"]);
        exit;
    }

    // A. Cari ID User Terlebih Dahulu
    $q_user = mysqli_query($konek, "SELECT id FROM users WHERE email = '$email' LIMIT 1");
    if (mysqli_num_rows($q_user) == 0) {
        echo json_encode(["status" => "error", "message" => "User tidak ditemukan di database"]);
        exit;
    }
    $user_data = mysqli_fetch_assoc($q_user);
    $id_pasien = $user_data['id'];

    // B. Cek apakah hari ini sudah ada input
    $cek = mysqli_query($konek, "SELECT id FROM monitoring_mental WHERE email = '$email' AND tanggal_input = '$tanggal_input'");
    
    if (mysqli_num_rows($cek) > 0) {
        $query = "UPDATE monitoring_mental 
                  SET mood = '$mood', catatan_jurnal = '$catatan' 
                  WHERE email = '$email' AND tanggal_input = '$tanggal_input'";
    } else {
        // C. Insert dengan variabel yang sudah pasti valid
        $query = "INSERT INTO monitoring_mental (email, mood, catatan_jurnal, tanggal_input, id_pasien) 
                  VALUES ('$email', '$mood', '$catatan', '$tanggal_input', '$id_pasien')";
    }

    if (mysqli_query($konek, $query)) {
        echo json_encode(["status" => "success", "message" => "Data berhasil disimpan"]);
    } else {
        echo json_encode(["status" => "error", "message" => "SQL Error: " . mysqli_error($konek)]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Metode request harus POST"]);
}
?>

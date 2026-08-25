<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "db_healu";

$konek = mysqli_connect($host, $user, $pass, $db);

if (!$konek) {
    // Gunakan echo dan exit agar Flutter menerima JSON, bukan teks error mentah
    echo json_encode(["status" => "error", "message" => "Koneksi ke database MySQL gagal"]);
    exit();
}
?>

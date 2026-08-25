<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

$konek = mysqli_connect("localhost", "root", "", "db_healu");

$id = $_POST['id'];
$query = mysqli_query($konek, "DELETE FROM kontak_darurat WHERE id = '$id'");

if ($query) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>

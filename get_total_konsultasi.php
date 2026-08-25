<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$stmt = $konek->prepare("SELECT COUNT(*) as total FROM konsultasi");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'total' => $row['total'] ?? 0
]);

$stmt->close();
$konek->close();
?>

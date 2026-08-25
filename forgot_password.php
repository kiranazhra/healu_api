<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json; charset=UTF-8");
mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

try {
    // Database diatur ke db_healu sesuai konfigurasi aslimu
    $conn = new mysqli("localhost", "root", "", "db_healu");
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal terhubung ke database server Laragon: " . $e->getMessage()
    ]);
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Silakan masukkan email Anda terlebih dahulu!"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Email tidak terdaftar di sistem HealU!"]);
        exit();
    }

    $to = $email;
    $subject = "Reset Kata Sandi Akun HealU 💚";
    $message = "Halo,\n\nKami menerima permintaan untuk mengatur ulang kata sandi akun HealU Anda.\nSilakan klik tautan di bawah ini untuk mengubah kata sandi:\n\nhttp://localhost/healu_api/reset_page.php?email=" . urlencode($email);
    $headers = "From: no-reply@healu.com";

    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(["status" => "success", "message" => "Email pemulihan kata sandi berhasil dikirim!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mengirimkan email. Periksa fitur MailCatcher Laragon Anda."]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Terjadi kesalahan pada server: " . $e->getMessage()]);
} finally {
    $conn->close();
}
?>

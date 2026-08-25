<?php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email dan password wajib diisi!"]);
        exit;
    }

    $stmt = mysqli_prepare($konek, "SELECT id, nama_lengkap, email, role, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $eksekusi = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($eksekusi) > 0) {
        $user = mysqli_fetch_assoc($eksekusi);

        // Bandingkan password sebagai teks biasa
        if ($password === $user['password']) {
            echo json_encode([
                "status" => "success",
                "message" => "Login berhasil! Selamat datang " . $user['nama_lengkap'],
                "data" => [
                    "id" => $user['id'],
                    "nama_lengkap" => $user['nama_lengkap'],
                    "email" => $user['email'],
                    "role" => $user['role']
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Email atau password salah!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email atau password salah!"]);
    }

    mysqli_stmt_close($stmt);
}
?>
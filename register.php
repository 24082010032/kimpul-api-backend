<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$nama = $data['nama'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($nama) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Semua field harus diisi"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $email, $hashedPassword);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Registrasi berhasil"]);
} catch (mysqli_sql_exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        echo json_encode(["status" => "error", "message" => "Email sudah terdaftar"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registrasi gagal: " . $e->getMessage()]);
    }
}

$conn->close();
?>
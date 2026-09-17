<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email dan password harus diisi"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, nama, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        unset($user['password']);
        echo json_encode(["status" => "success", "message" => "Login berhasil", "data" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "Password salah"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Email tidak ditemukan"]);
}

$stmt->close();
$conn->close();
?>
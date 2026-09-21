<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli("localhost", "root", "", "flutter_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

$id = $_POST['id'] ?? '';
$nama = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($id)) {
    echo json_encode(["status" => "error", "message" => "ID user tidak boleh kosong"]);
    exit();
}

$fotoName = null;

// Cek apakah ada file foto yang diunggah
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "uploads/";
    
    // Buat folder uploads jika belum ada
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
    // Nama file unik berdasarkan time & ID user
    $fotoName = "profile_" . $id . "_" . time() . "." . $fileExtension;
    $targetFilePath = $targetDir . $fotoName;

    if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
        echo json_encode(["status" => "error", "message" => "Gagal mengunggah foto ke server"]);
        exit();
    }
}

// Query Update Database
if ($fotoName != null) {
    // Jika upload foto baru
    $query = "UPDATE users SET nama='$nama', email='$email', foto='$fotoName' WHERE id='$id'";
} else {
    // Jika tidak ubah foto
    $query = "UPDATE users SET nama='$nama', email='$email' WHERE id='$id'";
}

if ($conn->query($query) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => "Profil berhasil diperbarui",
        "foto" => $fotoName
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal memperbarui database: " . $conn->error]);
}

$conn->close();
?>
<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$guru_id = $_SESSION['user']['id'];

$judul = trim($_POST['judul']);
$deskripsi = trim($_POST['deskripsi']);
$deadline = $_POST['deadline'];
$id_kelas = $_POST['id_kelas'];
$id_pelajaran = $_POST['id_pelajaran'];

// Proses upload file
if ($_FILES['file_tugas']['error'] !== UPLOAD_ERR_OK) {
    die("Error saat upload file.");
}

$file_tmp = $_FILES['file_tugas']['tmp_name'];
$file_ext = strtolower(pathinfo($_FILES['file_tugas']['name'], PATHINFO_EXTENSION));
$allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
$file_name = uniqid('tugas_', true) . "." . $file_ext;
$upload_path = "../uploads/tugas/" . $file_name;

if (!is_dir("../uploads/tugas")) {
    mkdir("../uploads/tugas", 0777, true);
}

if (!in_array($file_ext, $allowed)) {
    die("Format file tidak diperbolehkan.");
}

if (!move_uploaded_file($file_tmp, $upload_path)) {
    die("Gagal memindahkan file.";
)}

try {
    $stmt = $pdo->prepare("
        INSERT INTO tugas (judul, deskripsi, deadline, file_path, id_pelajaran, id_kelas, id_guru)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $judul,
        $deskripsi,
        $deadline,
        $upload_path,
        $id_pelajaran,
        $id_kelas,
        $guru_id
    ]);

    header("Location: dashboard.php?success=tugas");
    exit;
} catch (PDOException $e) {
    die("Gagal menyimpan tugas: " . $e->getMessage());
}
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$guru_id = $_SESSION['user']['id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kelas_id = $_POST['kelas_id'];
    $pelajaran_id = $_POST['pelajaran_id'];
    $deadline = $_POST['deadline'];

    // Validasi wajib
    if (empty($judul) || empty($kelas_id) || empty($pelajaran_id) || empty($deadline)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Upload file
        $upload_dir = "../uploads/assignments/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_path = null;
        if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['file_tugas']['tmp_name'];
            $name = basename($_FILES['file_tugas']['name']);
            $new_file = uniqid('assignment_') . '_' . $name;
            $target = $upload_dir . $new_file;

            if (move_uploaded_file($tmp_name, $target)) {
                $file_path = $target;
            } else {
                $error = "Gagal mengunggah file.";
            }
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO assignments 
                        (judul, deskripsi, kelas_id, pelajaran_id, id_guru, deadline, file_path)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$judul, $deskripsi, $kelas_id, $pelajaran_id, $guru_id, $deadline, $file_path]);

                $success = "Tugas berhasil diupload!";
            } catch (PDOException $e) {
                $error = "Gagal menyimpan ke database: " . $e->getMessage();
            }
        }
    }
} else {
    $error = "Permintaan tidak valid.";
}

$_SESSION['error'] = $error;
$_SESSION['success'] = $success;
header("Location: upload_tugas.php");
exit;
?>
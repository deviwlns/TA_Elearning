<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$siswa_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_materi'])) {
    $id_materi = $_POST['id_materi'];

    // Cek apakah sudah ada tugas ini
    $stmt_cek = $pdo->prepare("SELECT * FROM tugas_siswa WHERE id_siswa = ? AND id_materi = ?");
    $stmt_cek->execute([$siswa_id, $id_materi]);

    if ($stmt_cek->fetch()) {
        $error = "Tugas ini sudah pernah dikumpulkan.";
        header("Location: tugas_saya.php?error=" . urlencode($error));
        exit;
    }

    // Upload file jawaban
    if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['pdf', 'docx', 'pptx', 'xlsx', 'txt'];
        $file_name = $_FILES['file_jawaban']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error = "Format file tidak diperbolehkan.";
            header("Location: tugas_saya.php?error=" . urlencode($error));
            exit;
        }

        $new_file_name = uniqid('jawaban_', true) . '.' . $ext;
        $upload_dir = "../uploads/" . $new_file_name;

        if (!move_uploaded_file($_FILES['file_jawaban']['tmp_name'], $upload_dir)) {
            $error = "Gagal upload jawaban.";
            header("Location: tugas_saya.php?error=" . urlencode($error));
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO tugas_siswa (id_siswa, id_materi, jawaban_file, status, submitted_at) VALUES (?, ?, ?, 'dikumpulkan', NOW())");
            $stmt->execute([$siswa_id, $id_materi, $new_file_name]);

            $success = "Jawaban berhasil dikumpulkan!";
            header("Location: lihat_tugas.php?success=" . urlencode($success));
            exit;
        } catch (PDOException $e) {
            die("Gagal menyimpan jawaban: " . $e->getMessage());
        }
    } else {
        $error = "Silakan lampirkan file jawaban.";
        header("Location: tugas_saya.php?error=" . urlencode($error));
        exit;
    }
}
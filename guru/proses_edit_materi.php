<?php
include '../koneksi.php';
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_materi = $_POST['id_materi'];
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $id_kelas = $_POST['id_kelas'];

    // Update data dasar
    $stmt = $pdo->prepare("UPDATE materi SET judul = ?, deskripsi = ?, id_kelas = ? WHERE id_materi = ?");
    $stmt->execute([$judul, $deskripsi, $id_kelas, $id_materi]);

    // Upload file baru jika ada
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === 0) {
        $allowed_ext = ['pdf', 'docx', 'pptx', 'xlsx', 'txt'];
        $file_name = $_FILES['file_materi']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . "." . $file_ext;
            $upload_dir = "../uploads/" . $new_file_name;

            if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $upload_dir)) {
                // Hapus file lama jika ada
                $stmt_get_file = $pdo->prepare("SELECT file_materi FROM materi WHERE id_materi = ?");
                $stmt_get_file->execute([$id_materi]);
                $data = $stmt_get_file->fetch(PDO::FETCH_ASSOC);
                if ($data['file_materi'] && file_exists("../uploads/" . $data['file_materi'])) {
                    unlink("../uploads/" . $data['file_materi']);
                }

                // Update nama file di database
                $stmt_update_file = $pdo->prepare("UPDATE materi SET file_materi = ? WHERE id_materi = ?");
                $stmt_update_file->execute([$new_file_name, $id_materi]);
            } else {
                echo "Gagal upload file.";
                exit();
            }
        } else {
            echo "Format file tidak diperbolehkan.";
            exit();
        }
    }

    header("Location: daftar_materi.php?status=sukses");
    exit();
}
<?php
session_start();
include '../db.php';

if ($_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $kelas_id = $_POST['kelas_id'];
    $pelajaran_id = $_POST['pelajaran_id'];
    $deadline = $_POST['deadline'];
    $id_guru = $_SESSION['user']['id'];

    $stmt = $pdo->prepare("
        INSERT INTO assignments (judul, deskripsi, kelas_id, pelajaran_id, id_guru, deadline)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$judul, $deskripsi, $kelas_id, $pelajaran_id, $id_guru, $deadline]);

    header("Location: dashboard_guru.php");
    exit;
}
?>
<!-- Tambahkan form HTML untuk input judul, deskripsi, dll -->
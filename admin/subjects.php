<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Hapus mata pelajaran jika ada parameter delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: subjects.php?deleted=1");
    exit;
}

// Ambil semua mata pelajaran
$stmt = $pdo->query("SELECT * FROM subjects ORDER BY nama_pelajaran ASC");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Mata Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Mata Pelajaran</h2>
    <a href="add_subject.php" class="btn btn-success mb-3">Tambah Mata Pelajaran</a> <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali</a>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Mata pelajaran berhasil dihapus.</div>
    <?php endif; ?>

    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Mata pelajaran baru berhasil ditambahkan.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Mata pelajaran berhasil diperbarui.</div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pelajaran</th>
                <th>Deskripsi</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td><?= $subject['id'] ?></td>
                    <td><?= htmlspecialchars($subject['nama_pelajaran']) ?></td>
                    <td><?= nl2br(htmlspecialchars($subject['deskripsi'])) ?></td>
                    <td><?= $subject['created_at'] ?></td>
                    <td>
                        <a href="edit_subject.php?id=<?= $subject['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $subject['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-sm btn-danger">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
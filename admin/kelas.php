<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Hapus kelas jika ada parameter delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: kelas.php?deleted=1");
    exit;
}

// Ambil semua kelas beserta info wali kelas
$stmt = $pdo->query("
    SELECT k.id, k.nama_kelas, k.created_at, u.username AS wali_kelas 
    FROM kelas k
    LEFT JOIN users u ON k.wali_kelas_id = u.id
    ORDER BY k.nama_kelas ASC
");
$kelas_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Kelola Kelas</h2>
    <a href="add_kelas.php" class="btn btn-success mb-3">Tambah Kelas</a> <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali</a>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Kelas berhasil dihapus.</div>
    <?php endif; ?>

    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Kelas baru berhasil ditambahkan.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Kelas berhasil diperbarui.</div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Kelas</th>
                <th>Wali Kelas</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas_list as $kelas): ?>
                <tr>
                    <td><?= $kelas['id'] ?></td>
                    <td><?= htmlspecialchars($kelas['nama_kelas']) ?></td>
                    <td><?= $kelas['wali_kelas'] ?: '-' ?></td>
                    <td><?= $kelas['created_at'] ?></td>
                    <td>
                        <a href="edit_kelas.php?id=<?= $kelas['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $kelas['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-sm btn-danger">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
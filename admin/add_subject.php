<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include '../db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelajaran = trim($_POST['nama_pelajaran']);
    $deskripsi = trim($_POST['deskripsi']);

    if (empty($nama_pelajaran)) {
        $error = "Nama pelajaran harus diisi.";
    } else {
        try {
            // Cek apakah nama pelajaran sudah ada
            $stmt = $pdo->prepare("SELECT * FROM subjects WHERE nama_pelajaran = ?");
            $stmt->execute([$nama_pelajaran]);
            if ($stmt->rowCount() > 0) {
                $error = "Nama pelajaran sudah terdaftar.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO subjects (nama_pelajaran, deskripsi) VALUES (?, ?)");
                $stmt->execute([$nama_pelajaran, $deskripsi]);
                header("Location: subjects.php?added=1");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mata Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Tambah Mata Pelajaran</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nama_pelajaran" class="form-label">Nama Pelajaran</label>
            <input type="text" name="nama_pelajaran" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
            <textarea name="deskripsi" class="form-control" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="subjects.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>